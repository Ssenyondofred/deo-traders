<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $warehouse = $this->inventory->defaultWarehouse();

        $query = Product::query()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->with(['stockBalance' => fn ($q) => $q->where('warehouse_id', $warehouse->id)]);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $items = $products->map(function (Product $product) {
            $balance = $product->stockBalance;
            $totals = $this->inventory->movementTotals($product->id);

            $qtyOnHand = (float) ($balance?->qty_on_hand ?? 0);
            $avgCost = (float) ($balance?->avg_cost ?? 0);
            $unitPrice = $product->currentUnitPrice();

            return (object) [
                'product' => $product,
                'qty_in' => $totals['qty_in'],
                'qty_out' => $totals['qty_out'],
                'qty_on_hand' => $qtyOnHand,
                'avg_cost' => $avgCost,
                'unit_price' => $unitPrice,
                'stock_value' => $qtyOnHand * $avgCost,
            ];
        });

        $categories = Product::where('track_stock', true)
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('inventory.index', compact('items', 'categories', 'warehouse'));
    }

    public function show(Product $product, Request $request)
    {
        $warehouse = $this->inventory->defaultWarehouse();
        $balance = $this->inventory->getOrCreateBalance($product->id, $warehouse->id);
        $totals = $this->inventory->movementTotals(
            $product->id,
            $request->date_from,
            $request->date_to
        );

        $movements = StockMovement::with('creator')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('movement_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('movement_date', '<=', $d))
            ->when($request->movement_type, fn ($q, $t) => $q->where('movement_type', $t))
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.show', compact(
            'product',
            'balance',
            'totals',
            'movements',
            'warehouse'
        ));
    }
}
