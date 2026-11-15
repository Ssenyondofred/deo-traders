<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function __construct(private ActivityLogService $activityLog)
    {
    }

    public function defaultWarehouse(): Warehouse
    {
        return Warehouse::where('is_default', true)->where('is_active', true)->first()
            ?? Warehouse::where('is_active', true)->firstOrFail();
    }

    public function getOrCreateBalance(int $productId, ?int $warehouseId = null): StockBalance
    {
        $warehouseId ??= $this->defaultWarehouse()->id;

        return StockBalance::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['qty_on_hand' => 0, 'avg_cost' => 0]
        );
    }

    public function recordMovement(
        int $productId,
        StockMovementType $type,
        float $qty,
        float $unitCost = 0,
        ?string $notes = null,
        ?string $batchNo = null,
        \DateTimeInterface|string|null $movementDate = null,
        ?Model $reference = null,
    ): StockMovement {
        $movementDate = $this->parseMovementDate($movementDate);
        if ($qty <= 0) {
            throw new InvalidArgumentException('Qty harus lebih dari 0.');
        }

        $direction = $type->direction();
        $warehouse = $this->defaultWarehouse();

        return DB::transaction(function () use (
            $productId,
            $type,
            $qty,
            $unitCost,
            $notes,
            $batchNo,
            $movementDate,
            $reference,
            $direction,
            $warehouse
        ) {
            $balance = StockBalance::where('warehouse_id', $warehouse->id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = StockBalance::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $productId,
                    'qty_on_hand' => 0,
                    'avg_cost' => 0,
                ]);
            }

            $qtyBefore = (float) $balance->qty_on_hand;

            if ($direction === 'out' && $qtyBefore < $qty) {
                throw new InvalidArgumentException(
                    'Stok tidak mencukupi. Tersedia: '.number_format($qtyBefore, 2)
                );
            }

            $qtyAfter = $direction === 'in'
                ? $qtyBefore + $qty
                : $qtyBefore - $qty;

            if ($direction === 'in' && $unitCost > 0) {
                $valueBefore = $qtyBefore * (float) $balance->avg_cost;
                $valueIn = $qty * $unitCost;
                $balance->avg_cost = $qtyAfter > 0
                    ? ($valueBefore + $valueIn) / $qtyAfter
                    : $unitCost;
            }

            $balance->qty_on_hand = $qtyAfter;
            $balance->save();

            $movement = StockMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $productId,
                'movement_date' => $movementDate,
                'movement_type' => $type->value,
                'direction' => $direction,
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => round($qty * $unitCost, 2),
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'batch_no' => $batchNo,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            $this->activityLog->log(
                'stock_movement',
                'inventory',
                $movement,
                sprintf('%s %s — %s', $direction === 'in' ? 'Masuk' : 'Keluar', $qty, Product::find($productId)?->name)
            );

            return $movement;
        });
    }

    public function initializeBalances(): int
    {
        $warehouse = $this->defaultWarehouse();
        $count = 0;

        Product::where('is_active', true)->where('track_stock', true)->each(function (Product $product) use ($warehouse, &$count) {
            StockBalance::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                ['qty_on_hand' => 0, 'avg_cost' => 0]
            );
            $count++;
        });

        return $count;
    }

    public function movementTotals(int $productId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = StockMovement::where('product_id', $productId);

        if ($dateFrom) {
            $query->whereDate('movement_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('movement_date', '<=', $dateTo);
        }

        $in = (clone $query)->where('direction', 'in')->sum('qty');
        $out = (clone $query)->where('direction', 'out')->sum('qty');

        return [
            'qty_in' => (float) $in,
            'qty_out' => (float) $out,
        ];
    }

    private function parseMovementDate(\DateTimeInterface|string|null $movementDate): \DateTimeInterface
    {
        if ($movementDate === null) {
            return now();
        }

        if ($movementDate instanceof \DateTimeInterface) {
            return $movementDate;
        }

        return Carbon::parse($movementDate);
    }
}
