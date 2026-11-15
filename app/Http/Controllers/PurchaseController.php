<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->paginate(20);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $paid = $request->input('paid', 0);
        $total = $request->input('total', 0);
        $balance = $total - $paid;

        $purchase = Purchase::create([
            'purchase_number' => 'PO-' . now()->format('Ymd') . '-' . str_pad((Purchase::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'supplier_id'     => $request->input('supplier_id') ?: null,
            'purchase_date'   => now()->toDateString(),
            'subtotal'        => $total,
            'total'           => $total,
            'paid'            => $paid,
            'balance'         => $balance,
            'status'          => $request->input('status', 'partial'),
            'notes'           => $request->input('notes'),
        ]);

        $names  = (array) $request->input('product_names', []);
        $qtys   = (array) $request->input('qtys', []);
        $prices = (array) $request->input('prices', []);

        foreach ($names as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            PurchaseItem::create([
                'purchase_id'  => $purchase->id,
                'product_name' => $name,
                'quantity'     => $qtys[$i] ?? 1,
                'unit_price'   => $prices[$i] ?? 0,
                'line_total'   => ($qtys[$i] ?? 1) * ($prices[$i] ?? 0),
            ]);
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase saved.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'items');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $paid = $request->input('paid', 0);
        $total = $request->input('total', 0);
        $balance = $total - $paid;

        $purchase->update([
            'supplier_id'   => $request->input('supplier_id') ?: null,
            'subtotal'      => $total,
            'total'         => $total,
            'paid'          => $paid,
            'balance'       => $balance,
            'status'        => $request->input('status', 'partial'),
            'notes'         => $request->input('notes'),
        ]);

        $purchase->items()->delete();

        $names  = (array) $request->input('product_names', []);
        $qtys   = (array) $request->input('qtys', []);
        $prices = (array) $request->input('prices', []);

        foreach ($names as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            PurchaseItem::create([
                'purchase_id'  => $purchase->id,
                'product_name' => $name,
                'quantity'     => $qtys[$i] ?? 1,
                'unit_price'   => $prices[$i] ?? 0,
                'line_total'   => ($qtys[$i] ?? 1) * ($prices[$i] ?? 0),
            ]);
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase updated.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Deleted');
    }
}