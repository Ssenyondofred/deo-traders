<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Services\InventoryService;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->where('track_stock', true)
            ->orderBy('name')
            ->get();

        return view('inventory.adjustment-create', compact('products'));
    }

    public function store(StockAdjustmentRequest $request)
    {
        $type = match ($request->adjustment_type) {
            'opening' => StockMovementType::Opening,
            'adjustment_in' => StockMovementType::AdjustmentIn,
            default => StockMovementType::AdjustmentOut,
        };

        if ($type !== StockMovementType::AdjustmentOut && $request->direction === 'out') {
            return back()->withInput()->with('error', 'Tipe penyesuaian tidak sesuai arah stok.');
        }

        try {
            $this->inventory->recordMovement(
                productId: (int) $request->product_id,
                type: $type,
                qty: (float) $request->qty,
                unitCost: (float) ($request->unit_cost ?? 0),
                notes: $request->notes,
                batchNo: $request->batch_no,
                movementDate: $request->movement_date,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $product = Product::find($request->product_id);

        return redirect()
            ->route('inventory.show', $product)
            ->with('success', 'Penyesuaian stok berhasil dicatat.');
    }
}
