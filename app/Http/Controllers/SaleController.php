<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('customer')->latest()->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();
        return view('sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $paid = floatval($request->input('paid', 0));
        $total = $request->input('total', 0);
        $balance = $total - $paid;

        $sale = Sale::create([
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad((Sale::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'customer_id'    => $request->input('customer_id') ?: null,
            'customer_name'  => $request->input('customer_name'),
            'subtotal'       => $total,
            'total'          => $total,
            'paid'           => $paid,
            'balance'        => $balance,
            'payment_status' => $request->input('status', 'partial'),
            'status'         => 'completed',
            'note'           => $request->input('notes'),
        ]);

        $names  = (array) $request->input('product_names', []);
        $qtys   = (array) $request->input('qtys', []);
        $prices = (array) $request->input('prices', []);

        foreach ($names as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            SaleItem::create([
                'sale_id'      => $sale->id,
                'product_name' => $name,
                'quantity'     => $qtys[$i] ?? 1,
                'unit_price'   => $prices[$i] ?? 0,
                'line_total'   => ($qtys[$i] ?? 1) * ($prices[$i] ?? 0),
            ]);
        }

        return redirect()->route('sales.show', $sale);
    }

    public function show(Sale $sale)
    {
        $sale->load('customer', 'items');
        return view('sales.invoice', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $sale->load('items');
        $customers = Customer::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();
        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $paid = floatval($request->input('paid', 0));
        $total = $request->input('total', 0);
        $balance = $total - $paid;

        $sale->update([
            'customer_id'    => $request->input('customer_id') ?: null,
            'customer_name'  => $request->input('customer_name'),
            'subtotal'       => $total,
            'total'          => $total,
            'paid'           => $paid,
            'balance'        => $balance,
            'payment_status' => $request->input('status', 'partial'),
        ]);

        $sale->items()->delete();

        $names  = (array) $request->input('product_names', []);
        $qtys   = (array) $request->input('qtys', []);
        $prices = (array) $request->input('prices', []);

        foreach ($names as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            SaleItem::create([
                'sale_id'      => $sale->id,
                'product_name' => $name,
                'quantity'     => $qtys[$i] ?? 1,
                'unit_price'   => $prices[$i] ?? 0,
                'line_total'   => ($qtys[$i] ?? 1) * ($prices[$i] ?? 0),
            ]);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Deleted');
    }

    public function trash()
    {
        $sales = Sale::onlyTrashed()
            ->with('customer')
            ->latest('deleted_at')
            ->paginate(20);

        return view('sales.trash', compact('sales'));
    }

    public function restore($id)
    {
        $sale = Sale::onlyTrashed()->findOrFail($id);
        $sale->restore();

        return redirect()->route('sales.trash')->with('success', 'Invoice restored.');
    }
}