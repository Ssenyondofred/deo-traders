<?php

namespace App\Services;

use App\Enums\ArStatus;
use App\Enums\DocumentStatus;
use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Models\ArDocument;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleBomRule;
use App\Models\SaleItem;
use App\Models\StockBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleService
{
    public function __construct(
        private DocumentNumberService $documentNumbers,
        private InventoryService $inventory,
        private JournalService $journal,
        private CashBankService $cashBank,
        private ActivityLogService $activityLog,
    ) {
    }

    public function calculateTotals(float $qty, float $unitPrice, float $discount = 0, bool $isTaxable = false, float $taxPercent = 11): array
    {
        $subtotal = round($qty * $unitPrice, 2);
        $discount = round(max(0, $discount), 2);
        $taxableBase = max(0, $subtotal - $discount);
        $taxAmount = $isTaxable ? round($taxableBase * ($taxPercent / 100), 2) : 0;
        $grandTotal = round($taxableBase + $taxAmount, 2);

        return compact('subtotal', 'discount', 'taxAmount', 'grandTotal');
    }

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $product = $this->finishedProduct();
            $totals = $this->calculateTotals(
                (float) $data['qty'],
                (float) $data['unit_price'],
                (float) ($data['discount'] ?? 0),
                (bool) ($data['is_taxable'] ?? false),
                (float) ($data['tax_percent'] ?? 11),
            );

            $sale = Sale::create([
                'sale_no' => $this->documentNumbers->next('SO'),
                'sale_date' => $data['sale_date'],
                'customer_id' => $data['customer_id'],
                'payment_method' => $data['payment_method'],
                'cash_bank_account_id' => $data['cash_bank_account_id'] ?? null,
                'status' => DocumentStatus::Draft->value,
                'subtotal' => $totals['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'is_taxable' => $data['is_taxable'] ?? false,
                'tax_percent' => $data['is_taxable'] ? ($data['tax_percent'] ?? 11) : 0,
                'tax_amount' => $totals['taxAmount'],
                'grand_total' => $totals['grandTotal'],
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncSaleItems($sale, $product->id, (float) $data['qty'], (float) $data['unit_price']);
            $this->activityLog->log('created', 'sales', $sale, "Buat SO {$sale->sale_no}");

            return $sale->load(['items.product', 'customer']);
        });
    }

    public function update(Sale $sale, array $data): Sale
    {
        if (! $sale->isEditable()) {
            throw new InvalidArgumentException('Penjualan tidak dapat diubah pada status ini.');
        }

        return DB::transaction(function () use ($sale, $data) {
            $product = $this->finishedProduct();
            $totals = $this->calculateTotals(
                (float) $data['qty'],
                (float) $data['unit_price'],
                (float) ($data['discount'] ?? 0),
                (bool) ($data['is_taxable'] ?? false),
                (float) ($data['tax_percent'] ?? 11),
            );

            $sale->update([
                'sale_date' => $data['sale_date'],
                'customer_id' => $data['customer_id'],
                'payment_method' => $data['payment_method'],
                'cash_bank_account_id' => $data['cash_bank_account_id'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'is_taxable' => $data['is_taxable'] ?? false,
                'tax_percent' => $data['is_taxable'] ? ($data['tax_percent'] ?? 11) : 0,
                'tax_amount' => $totals['taxAmount'],
                'grand_total' => $totals['grandTotal'],
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'rejection_reason' => null,
                'status' => DocumentStatus::Draft->value,
            ]);

            $sale->items()->delete();
            $this->syncSaleItems($sale, $product->id, (float) $data['qty'], (float) $data['unit_price']);
            $this->activityLog->log('updated', 'sales', $sale, "Ubah SO {$sale->sale_no}");

            return $sale->fresh(['items.product', 'customer']);
        });
    }

    public function submit(Sale $sale): Sale
    {
        if (! in_array($sale->status, [DocumentStatus::Draft->value, DocumentStatus::Rejected->value], true)) {
            throw new InvalidArgumentException('Hanya draft yang bisa diajukan.');
        }

        $this->validateBeforePost($sale, checkStock: false);

        $sale->update([
            'status' => DocumentStatus::PendingApproval->value,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->activityLog->log('submitted', 'sales', $sale, "Ajukan SO {$sale->sale_no}");

        return $sale;
    }

    public function approve(Sale $sale): Sale
    {
        if ($sale->status !== DocumentStatus::PendingApproval->value) {
            throw new InvalidArgumentException('Penjualan tidak menunggu approval.');
        }

        return DB::transaction(function () use ($sale) {
            $sale->load(['items.product', 'customer', 'cashBankAccount']);
            $this->validateBeforePost($sale, checkStock: true);

            $totalCogs = 0;
            $stockOuts = $this->buildStockOuts($sale);

            foreach ($stockOuts as $out) {
                $avgCost = $this->getAvgCost($out['product_id']);
                $lineCogs = round($out['qty'] * $avgCost, 2);
                $totalCogs += $lineCogs;

                $this->inventory->recordMovement(
                    productId: $out['product_id'],
                    type: StockMovementType::SaleOut,
                    qty: $out['qty'],
                    unitCost: $avgCost,
                    notes: "Penjualan {$sale->sale_no} — {$out['product_name']}",
                    movementDate: $sale->sale_date,
                    reference: $sale,
                );
            }

            $journal = $this->postJournal($sale, $totalCogs);

            if ($sale->payment_method === PaymentMethod::Cash->value) {
                $this->cashBank->record(
                    $sale->cashBankAccount,
                    'in',
                    (float) $sale->grand_total,
                    "Penjualan {$sale->sale_no}",
                    $sale,
                    $journal,
                    $sale->sale_date,
                );
            } else {
                $this->createArDocument($sale);
            }

            $sale->update([
                'status' => DocumentStatus::Posted->value,
                'total_cogs' => $totalCogs,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'posted_at' => now(),
            ]);

            $this->activityLog->log('approved', 'sales', $sale, "Approve SO {$sale->sale_no}");

            return $sale->fresh(['items.product', 'customer', 'arDocument', 'journalEntry.lines.account']);
        });
    }

    public function reject(Sale $sale, string $reason): Sale
    {
        if ($sale->status !== DocumentStatus::PendingApproval->value) {
            throw new InvalidArgumentException('Penjualan tidak menunggu approval.');
        }

        $sale->update([
            'status' => DocumentStatus::Rejected->value,
            'rejection_reason' => $reason,
        ]);

        $this->activityLog->log('rejected', 'sales', $sale, "Tolak SO {$sale->sale_no}");

        return $sale;
    }

    public function bomPreview(float $saleQty): array
    {
        $product = $this->finishedProduct();
        $rules = SaleBomRule::with('componentProduct')
            ->where('finished_product_id', $product->id)
            ->where('is_active', true)
            ->get();

        return $rules->map(function (SaleBomRule $rule) use ($saleQty) {
            $qty = round($saleQty * (float) $rule->qty_per_unit, 4);

            return [
                'product_id' => $rule->component_product_id,
                'name' => $rule->componentProduct->name,
                'code' => $rule->componentProduct->code,
                'unit' => $rule->componentProduct->unit,
                'qty' => $qty,
                'qty_per_unit' => $rule->qty_per_unit,
            ];
        })->all();
    }

    private function syncSaleItems(Sale $sale, int $productId, float $qty, float $unitPrice): void
    {
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productId,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'line_total' => round($qty * $unitPrice, 2),
            'is_main_product' => true,
            'is_bom_component' => false,
        ]);

        foreach ($this->bomPreview($qty) as $bom) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $bom['product_id'],
                'qty' => $bom['qty'],
                'unit_price' => 0,
                'line_total' => 0,
                'is_main_product' => false,
                'is_bom_component' => true,
            ]);
        }
    }

    /** @return array<int, array{product_id: int, product_name: string, qty: float}> */
    private function buildStockOuts(Sale $sale): array
    {
        $outs = [];
        foreach ($sale->items as $item) {
            $outs[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'qty' => (float) $item->qty,
            ];
        }

        return $outs;
    }

    private function validateBeforePost(Sale $sale, bool $checkStock): void
    {
        $sale->loadMissing('items.product');

        if ($sale->items->where('is_main_product', true)->isEmpty()) {
            throw new InvalidArgumentException('Item penjualan tidak lengkap.');
        }

        if ($sale->payment_method === PaymentMethod::Cash->value && ! $sale->cash_bank_account_id) {
            throw new InvalidArgumentException('Akun kas/bank wajib untuk penjualan tunai.');
        }

        if ($sale->payment_method === PaymentMethod::Credit->value && ! $sale->due_date) {
            throw new InvalidArgumentException('Jatuh tempo wajib untuk penjualan kredit.');
        }

        if ($checkStock) {
            foreach ($this->buildStockOuts($sale) as $out) {
                $balance = $this->inventory->getOrCreateBalance($out['product_id']);
                if ((float) $balance->qty_on_hand < $out['qty']) {
                    throw new InvalidArgumentException(
                        "Stok {$out['product_name']} tidak cukup. Tersedia: ".number_format($balance->qty_on_hand, 2)
                    );
                }
            }
        }
    }

    private function getAvgCost(int $productId): float
    {
        $warehouse = $this->inventory->defaultWarehouse();
        $balance = StockBalance::where('warehouse_id', $warehouse->id)
            ->where('product_id', $productId)
            ->first();

        return (float) ($balance?->avg_cost ?? 0);
    }

    private function postJournal(Sale $sale, float $totalCogs): \App\Models\JournalEntry
    {
        $revenueAccountId = $this->journal->accountIdByCode('4000.01');
        $cogsAccountId = $this->journal->accountIdByCode('5000.01');
        $inventoryAccountId = $this->journal->accountIdByCode('1200.01');
        $receivableAccountId = $this->journal->accountIdByCode('1100.01');
        $taxAccountId = $this->journal->accountIdByCode('2100.07');

        $taxableBase = (float) $sale->subtotal - (float) $sale->discount;
        $lines = [];

        if ($sale->payment_method === PaymentMethod::Cash->value) {
            $lines[] = [
                'account_id' => $sale->cashBankAccount->account_id,
                'debit' => (float) $sale->grand_total,
                'credit' => 0,
                'description' => "Kas masuk {$sale->sale_no}",
            ];
        } else {
            $lines[] = [
                'account_id' => $receivableAccountId,
                'debit' => (float) $sale->grand_total,
                'credit' => 0,
                'description' => "Piutang {$sale->sale_no}",
            ];
        }

        $lines[] = [
            'account_id' => $revenueAccountId,
            'debit' => 0,
            'credit' => $taxableBase,
            'description' => "Penjualan {$sale->sale_no}",
        ];

        if ((float) $sale->tax_amount > 0) {
            $lines[] = [
                'account_id' => $taxAccountId,
                'debit' => 0,
                'credit' => (float) $sale->tax_amount,
                'description' => "PPN Keluaran {$sale->sale_no}",
            ];
        }

        if ($totalCogs > 0) {
            $lines[] = [
                'account_id' => $cogsAccountId,
                'debit' => $totalCogs,
                'credit' => 0,
                'description' => "HPP {$sale->sale_no}",
            ];
            $lines[] = [
                'account_id' => $inventoryAccountId,
                'debit' => 0,
                'credit' => $totalCogs,
                'description' => "Persediaan HPP {$sale->sale_no}",
            ];
        }

        // 1. Buat draft jurnalnya (Tanpa membawa data 'notes')
        $journalEntry = $this->journal->createManual([
            'entry_date'  => $sale->sale_date,
            'description' => "Jurnal penjualan {$sale->sale_no}",
            'lines'       => $lines, 
        ]);

        // 2. Lempar OBJEK $journalEntry ke dalam fungsi post()
        return $this->journal->post($journalEntry);
    }

    private function createArDocument(Sale $sale): ArDocument
    {
        return ArDocument::create([
            'document_no' => $sale->sale_no,
            'customer_id' => $sale->customer_id,
            'source_type' => Sale::class,
            'source_id' => $sale->id,
            'original_amount' => $sale->grand_total,
            'paid_amount' => 0,
            'balance_amount' => $sale->grand_total,
            'status' => ArStatus::Open->value,
            'due_date' => $sale->due_date ?? now()->addDays($sale->customer->payment_term_days)->toDateString(),
            'notes' => $sale->notes,
        ]);
    }

    private function finishedProduct(): Product
    {
        return Product::where('code', 'FG-CORN')->where('is_active', true)->firstOrFail();
    }
}
