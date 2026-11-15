<?php

namespace App\Services;

use App\Enums\ApStatus;
use App\Enums\DocumentStatus;
use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Models\ApDocument;
use App\Models\CashBankAccount;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        private DocumentNumberService $documentNumbers,
        private InventoryService $inventory,
        private JournalService $journal,
        private CashBankService $cashBank,
        private ActivityLogService $activityLog,
    ) {
    }

    public function calculateTotals(array $items, float $discount = 0, bool $isTaxable = false, float $taxPercent = 11): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += round((float) $item['qty'] * (float) $item['unit_price'], 2);
        }
        $subtotal = round($subtotal, 2);
        $discount = round(max(0, $discount), 2);
        $taxableBase = max(0, $subtotal - $discount);
        $taxAmount = $isTaxable ? round($taxableBase * ($taxPercent / 100), 2) : 0;
        $grandTotal = round($taxableBase + $taxAmount, 2);

        return compact('subtotal', 'discount', 'taxAmount', 'grandTotal');
    }

    public function create(array $data, array $items): Purchase
    {
        return DB::transaction(function () use ($data, $items) {
            $totals = $this->calculateTotals(
                $items,
                (float) ($data['discount'] ?? 0),
                (bool) ($data['is_taxable'] ?? false),
                (float) ($data['tax_percent'] ?? 11),
            );

            $purchase = Purchase::create([
                'purchase_no' => $this->documentNumbers->next('PO'),
                'purchase_date' => $data['purchase_date'],
                'supplier_id' => $data['supplier_id'],
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

            $this->syncItems($purchase, $items);
            $this->activityLog->log('created', 'purchases', $purchase, "Buat PO {$purchase->purchase_no}");

            return $purchase->load(['items.product', 'supplier']);
        });
    }

    public function update(Purchase $purchase, array $data, array $items): Purchase
    {
        if (! $purchase->isEditable()) {
            throw new InvalidArgumentException('Pembelian tidak dapat diubah pada status ini.');
        }

        return DB::transaction(function () use ($purchase, $data, $items) {
            $totals = $this->calculateTotals(
                $items,
                (float) ($data['discount'] ?? 0),
                (bool) ($data['is_taxable'] ?? false),
                (float) ($data['tax_percent'] ?? 11),
            );

            $purchase->update([
                'purchase_date' => $data['purchase_date'],
                'supplier_id' => $data['supplier_id'],
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

            $purchase->items()->delete();
            $this->syncItems($purchase, $items);
            $this->activityLog->log('updated', 'purchases', $purchase, "Ubah PO {$purchase->purchase_no}");

            return $purchase->fresh(['items.product', 'supplier']);
        });
    }

    public function submit(Purchase $purchase): Purchase
    {
        if ($purchase->status !== DocumentStatus::Draft->value && $purchase->status !== DocumentStatus::Rejected->value) {
            throw new InvalidArgumentException('Hanya draft yang bisa diajukan.');
        }

        $purchase->update([
            'status' => DocumentStatus::PendingApproval->value,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->activityLog->log('submitted', 'purchases', $purchase, "Ajukan approval PO {$purchase->purchase_no}");

        return $purchase;
    }

    public function approve(Purchase $purchase): Purchase
    {
        if ($purchase->status !== DocumentStatus::PendingApproval->value) {
            throw new InvalidArgumentException('Pembelian tidak menunggu approval.');
        }

        return DB::transaction(function () use ($purchase) {
            $purchase->load(['items.product', 'supplier', 'cashBankAccount']);

            $this->validateBeforePost($purchase);

            foreach ($purchase->items as $item) {
                $this->inventory->recordMovement(
                    productId: $item->product_id,
                    type: StockMovementType::PurchaseIn,
                    qty: (float) $item->qty,
                    unitCost: (float) $item->unit_price,
                    notes: "Pembelian {$purchase->purchase_no}",
                    movementDate: $purchase->purchase_date,
                    reference: $purchase,
                );
            }

            $journal = $this->postJournal($purchase);

            if ($purchase->payment_method === PaymentMethod::Cash->value) {
                $cashAccount = $purchase->cashBankAccount;
                $this->cashBank->record(
                    $cashAccount,
                    'out',
                    (float) $purchase->grand_total,
                    "Pembelian {$purchase->purchase_no}",
                    $purchase,
                    $journal,
                    $purchase->purchase_date,
                );
            } else {
                $this->createApDocument($purchase);
            }

            $purchase->update([
                'status' => DocumentStatus::Posted->value,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'posted_at' => now(),
            ]);

            $this->activityLog->log('approved', 'purchases', $purchase, "Approve & post PO {$purchase->purchase_no}");

            return $purchase->fresh(['items.product', 'supplier', 'apDocument', 'journalEntry.lines.account']);
        });
    }

    public function reject(Purchase $purchase, string $reason): Purchase
    {
        if ($purchase->status !== DocumentStatus::PendingApproval->value) {
            throw new InvalidArgumentException('Pembelian tidak menunggu approval.');
        }

        $purchase->update([
            'status' => DocumentStatus::Rejected->value,
            'rejection_reason' => $reason,
        ]);

        $this->activityLog->log('rejected', 'purchases', $purchase, "Tolak PO {$purchase->purchase_no}: {$reason}");

        return $purchase;
    }

    private function syncItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $lineTotal = round((float) $item['qty'] * (float) $item['unit_price'], 2);
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }

    private function validateBeforePost(Purchase $purchase): void
    {
        if ($purchase->items->isEmpty()) {
            throw new InvalidArgumentException('Minimal 1 item pembelian.');
        }

        if ($purchase->payment_method === PaymentMethod::Cash->value && ! $purchase->cash_bank_account_id) {
            throw new InvalidArgumentException('Akun kas/bank wajib dipilih untuk pembayaran tunai.');
        }

        if ($purchase->payment_method === PaymentMethod::Credit->value && ! $purchase->due_date) {
            throw new InvalidArgumentException('Tanggal jatuh tempo wajib untuk pembelian kredit.');
        }
    }

    private function postJournal(Purchase $purchase): \App\Models\JournalEntry
    {
        $inventoryAccountId = $this->journal->accountIdByCode('1200.01');
        $taxAccountId = $this->journal->accountIdByCode('1400.06');
        $payableAccountId = $this->journal->accountIdByCode('2000.01');

        $taxableBase = (float) $purchase->subtotal - (float) $purchase->discount;
        $lines = [
            [
                'account_id' => $inventoryAccountId,
                'debit' => $taxableBase,
                'credit' => 0,
                'description' => "Persediaan {$purchase->purchase_no}",
            ],
        ];

        if ((float) $purchase->tax_amount > 0) {
            $lines[] = [
                'account_id' => $taxAccountId,
                'debit' => (float) $purchase->tax_amount,
                'credit' => 0,
                'description' => "PPN Masukan {$purchase->purchase_no}",
            ];
        }

        if ($purchase->payment_method === PaymentMethod::Cash->value) {
            $cashCoaId = $purchase->cashBankAccount->account_id;
            $lines[] = [
                'account_id' => $cashCoaId,
                'debit' => 0,
                'credit' => (float) $purchase->grand_total,
                'description' => "Kas keluar {$purchase->purchase_no}",
            ];
        } else {
            $lines[] = [
                'account_id' => $payableAccountId,
                'debit' => 0,
                'credit' => (float) $purchase->grand_total,
                'description' => "Hutang usaha {$purchase->purchase_no}",
            ];
        }

        return $this->journal->post(
            $lines,
            'purchase',
            "Jurnal pembelian {$purchase->purchase_no}",
            $purchase,
            $purchase->purchase_date,
        );
    }

    private function createApDocument(Purchase $purchase): ApDocument
    {
        $supplier = $purchase->supplier;

        return ApDocument::create([
            'document_no' => $purchase->purchase_no,
            'supplier_id' => $purchase->supplier_id,
            'source_type' => Purchase::class,
            'source_id' => $purchase->id,
            'original_amount' => $purchase->grand_total,
            'paid_amount' => 0,
            'balance_amount' => $purchase->grand_total,
            'status' => ApStatus::Open->value,
            'due_date' => $purchase->due_date ?? now()->addDays($supplier->payment_term_days)->toDateString(),
            'notes' => $purchase->notes,
        ]);
    }
}
