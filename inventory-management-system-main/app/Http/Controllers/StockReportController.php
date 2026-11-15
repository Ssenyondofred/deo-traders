<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
class StockReportController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $warehouse = $this->inventory->defaultWarehouse();
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $query = Product::query()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->with(['stockBalance' => fn ($q) => $q->where('warehouse_id', $warehouse->id)]);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $rows = $products->map(function (Product $product) use ($dateFrom, $dateTo) {
            $totals = $this->inventory->movementTotals($product->id, $dateFrom, $dateTo);
            $balance = $product->stockBalance;
            $qtyOnHand = (float) ($balance?->qty_on_hand ?? 0);
            $avgCost = (float) ($balance?->avg_cost ?? 0);

            return (object) [
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->categoryLabel(),
                'unit' => $product->unit,
                'qty_in' => $totals['qty_in'],
                'qty_out' => $totals['qty_out'],
                'qty_on_hand' => $qtyOnHand,
                'avg_cost' => $avgCost,
                'stock_value' => $qtyOnHand * $avgCost,
            ];
        });

        $summary = (object) [
            'total_qty' => $rows->sum('qty_on_hand'),
            'total_value' => $rows->sum('stock_value'),
            'product_count' => $rows->count(),
        ];

        $categories = Product::where('track_stock', true)->distinct()->pluck('category');

        if ($request->get('export') === 'print') {
            return view('inventory.report-print', compact('rows', 'summary', 'warehouse', 'dateFrom', 'dateTo'));
        }

        return view('inventory.report', compact('rows', 'summary', 'categories', 'warehouse', 'dateFrom', 'dateTo'));
    }
}
