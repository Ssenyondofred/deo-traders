<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\ProductionMaterial;
use App\Models\ProductionOutput;
use App\Models\StockBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductionService
{
    public function __construct(
        private DocumentNumberService $documentNumbers,
        private InventoryService $inventory,
        private JournalService $journal,
        private ActivityLogService $activityLog,
    ) {
    }

    public function create(array $data): ProductionBatch
    {
        return DB::transaction(function () use ($data) {
            $batch = ProductionBatch::create([
                'batch_no' => $this->documentNumbers->next('PR'),
                'production_date' => $data['production_date'],
                'batch_label' => $data['batch_label'] ?? null,
                'status' => DocumentStatus::Draft->value,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncBatchLines($batch, $data);
            $this->activityLog->log('created', 'production', $batch, "Buat batch {$batch->batch_no}");

            return $batch->load(['materials.product', 'outputs.product']);
        });
    }

    public function update(ProductionBatch $batch, array $data): ProductionBatch
    {
        if (! $batch->isEditable()) {
            throw new InvalidArgumentException('Batch produksi tidak dapat diubah pada status ini.');
        }

        return DB::transaction(function () use ($batch, $data) {
            $batch->update([
                'production_date' => $data['production_date'],
                'batch_label' => $data['batch_label'] ?? null,
                'notes' => $data['notes'] ?? null,
                'rejection_reason' => null,
                'status' => DocumentStatus::Draft->value,
            ]);

            $batch->materials()->delete();
            $batch->outputs()->delete();
            $this->syncBatchLines($batch, $data);
            $this->activityLog->log('updated', 'production', $batch, "Ubah batch {$batch->batch_no}");

            return $batch->fresh(['materials.product', 'outputs.product']);
        });
    }

    public function submit(ProductionBatch $batch): ProductionBatch
    {
        if (! in_array($batch->status, [DocumentStatus::Draft->value, DocumentStatus::Rejected->value], true)) {
            throw new InvalidArgumentException('Hanya draft yang bisa diajukan.');
        }

        $this->validateBatchData($batch);

        $batch->update([
            'status' => DocumentStatus::PendingApproval->value,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->activityLog->log('submitted', 'production', $batch, "Ajukan batch {$batch->batch_no}");

        return $batch;
    }

    public function approve(ProductionBatch $batch): ProductionBatch
    {
        if ($batch->status !== DocumentStatus::PendingApproval->value) {
            throw new InvalidArgumentException('Batch tidak menunggu approval.');
        }

        return DB::transaction(function () use ($batch) {
            $batch->load(['materials.product', 'outputs.product']);
            $this->validateBatchData($batch);
            $this->validateStockAvailability($batch);

            $totalMaterialCost = 0;

            foreach ($batch->materials as $material) {
                $avgCost = $this->getAvgCost($material->product_id);
                $lineCost = round((float) $material->qty * $avgCost, 2);
                $material->update(['unit_cost' => $avgCost, 'line_cost' => $lineCost]);
                $totalMaterialCost += $lineCost;

                $this->inventory->recordMovement(
                    productId: $material->product_id,
                    type: StockMovementType::ProductionOut,
                    qty: (float) $material->qty,
                    unitCost: $avgCost,
                    notes: "Produksi {$batch->batch_no} — bahan {$material->product->name}",
                    batchNo: $batch->batch_no,
                    movementDate: $batch->production_date,
                    reference: $batch,
                );
            }

            $output = $batch->outputs->first();
            $outputQty = (float) $output->qty;
            $outputUnitCost = $outputQty > 0 ? round($totalMaterialCost / $outputQty, 4) : 0;
            $outputLineCost = round($outputQty * $outputUnitCost, 2);

            $output->update([
                'unit_cost' => $outputUnitCost,
                'line_cost' => $outputLineCost,
            ]);

            $this->inventory->recordMovement(
                productId: $output->product_id,
                type: StockMovementType::ProductionIn,
                qty: $outputQty,
                unitCost: $outputUnitCost,
                notes: "Produksi {$batch->batch_no} — hasil {$output->product->name}",
                batchNo: $batch->batch_no,
                movementDate: $batch->production_date,
                reference: $batch,
            );

            $this->postJournal($batch, $totalMaterialCost);

            $batch->update([
                'status' => DocumentStatus::Posted->value,
                'total_material_cost' => $totalMaterialCost,
                'total_output_qty' => $outputQty,
                'output_unit_cost' => $outputUnitCost,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'posted_at' => now(),
            ]);

            $this->activityLog->log('approved', 'production', $batch, "Approve batch {$batch->batch_no}");

            return $batch->fresh(['materials.product', 'outputs.product', 'journalEntry.lines.account']);
        });
    }

    public function reject(ProductionBatch $batch, string $reason): ProductionBatch
    {
        if ($batch->status !== DocumentStatus::PendingApproval->value) {
            throw new InvalidArgumentException('Batch tidak menunggu approval.');
        }

        $batch->update([
            'status' => DocumentStatus::Rejected->value,
            'rejection_reason' => $reason,
        ]);

        $this->activityLog->log('rejected', 'production', $batch, "Tolak batch {$batch->batch_no}");

        return $batch;
    }

    private function syncBatchLines(ProductionBatch $batch, array $data): void
    {
        $rawProduct = $this->productByCode('RM-CORN');
        $coalProduct = $this->productByCode('RM-COAL');
        $finishedProduct = $this->productByCode('FG-CORN');

        ProductionMaterial::create([
            'production_batch_id' => $batch->id,
            'product_id' => $rawProduct->id,
            'qty' => $data['raw_corn_qty'],
            'unit_cost' => 0,
            'line_cost' => 0,
        ]);

        ProductionMaterial::create([
            'production_batch_id' => $batch->id,
            'product_id' => $coalProduct->id,
            'qty' => $data['coal_qty'],
            'unit_cost' => 0,
            'line_cost' => 0,
        ]);

        ProductionOutput::create([
            'production_batch_id' => $batch->id,
            'product_id' => $finishedProduct->id,
            'qty' => $data['output_qty'],
            'unit_cost' => 0,
            'line_cost' => 0,
        ]);
    }

    private function validateBatchData(ProductionBatch $batch): void
    {
        $batch->load(['materials', 'outputs']);

        if ($batch->materials->isEmpty() || $batch->outputs->isEmpty()) {
            throw new InvalidArgumentException('Data bahan dan hasil produksi tidak lengkap.');
        }

        foreach ($batch->materials as $m) {
            if ((float) $m->qty <= 0) {
                throw new InvalidArgumentException('Qty bahan baku harus lebih dari 0.');
            }
        }

        if ((float) $batch->outputs->first()->qty <= 0) {
            throw new InvalidArgumentException('Qty hasil jagung kering harus lebih dari 0.');
        }
    }

    private function validateStockAvailability(ProductionBatch $batch): void
    {
        $batch->loadMissing('materials.product');

        foreach ($batch->materials as $material) {
            $balance = $this->inventory->getOrCreateBalance($material->product_id);
            if ((float) $balance->qty_on_hand < (float) $material->qty) {
                throw new InvalidArgumentException(
                    "Stok {$material->product->name} tidak cukup. Tersedia: ".number_format($balance->qty_on_hand, 2)
                );
            }
        }
    }

    private function getAvgCost(int $productId): float
    {
        $balance = StockBalance::where('product_id', $productId)
            ->whereHas('warehouse', fn ($q) => $q->where('is_default', true))
            ->first();

        return (float) ($balance?->avg_cost ?? 0);
    }

    private function postJournal(ProductionBatch $batch, float $totalCost): void
    {
        if ($totalCost <= 0) {
            return;
        }

        $accountId = $this->journal->accountIdByCode('1200.01');

        $this->journal->post(
            [
                [
                    'account_id' => $accountId,
                    'debit' => $totalCost,
                    'credit' => 0,
                    'description' => "Hasil produksi {$batch->batch_no}",
                ],
                [
                    'account_id' => $accountId,
                    'debit' => 0,
                    'credit' => $totalCost,
                    'description' => "Bahan produksi {$batch->batch_no}",
                ],
            ],
            'production',
            "Jurnal produksi {$batch->batch_no}",
            $batch,
            $batch->production_date,
        );
    }

    private function productByCode(string $code): Product
    {
        return Product::where('code', $code)->where('is_active', true)->firstOrFail();
    }
}
