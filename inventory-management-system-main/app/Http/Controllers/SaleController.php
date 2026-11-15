<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\CashBankAccount;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SaleController extends Controller
{
    public function __construct(private SaleService $sales)
    {
    }

    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'creator'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if (! auth()->user()->isAdmin()) {
            // $query->where('created_by', auth()->id());
        }

        $sales = $query->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $product = Product::where('code', 'FG-CORN')->where('is_active', true)->firstOrFail();
        $bomRules = $this->sales->bomPreview(1);

        return view('sales.form', [
            'sale' => null,
            'mainItem' => null,
            'product' => $product,
            'bomRules' => $bomRules,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'cashAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(SaleRequest $request)
    {
        try {
            $sale = $this->sales->create($request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = 'Penjualan disimpan sebagai draft.';

        if ($request->boolean('submit_after_save')) {
            $this->sales->submit($sale);
            $message = 'Penjualan disimpan dan diajukan untuk approval.';
        }

        return redirect()->route('sales.show', $sale)->with('success', $message);
    }

    public function show(Sale $sale)
    {
        $this->authorizeView($sale);

        $sale->load([
            'items.product',
            'customer',
            'cashBankAccount',
            'creator',
            'approver',
            'arDocument',
            'journalEntry.lines.account',
        ]);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $this->authorizeView($sale);

        if (! $sale->isEditable()) {
            return redirect()->route('sales.show', $sale)->with('error', 'Penjualan tidak dapat diedit.');
        }

        $sale->load('items');
        $main = $sale->items->firstWhere('is_main_product', true);
        $product = Product::where('code', 'FG-CORN')->firstOrFail();
        $bomRules = $this->sales->bomPreview((float) ($main?->qty ?? 1));

        return view('sales.form', [
            'sale' => $sale,
            'product' => $product,
            'mainItem' => $main,
            'bomRules' => $bomRules,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'cashAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(SaleRequest $request, Sale $sale)
    {
        $this->authorizeView($sale);

        try {
            $this->sales->update($sale, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->boolean('submit_after_save')) {
            $this->sales->submit($sale);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan diperbarui.');
    }

    public function submit(Sale $sale)
    {
        $this->authorizeView($sale);

        try {
            $this->sales->submit($sale);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Penjualan diajukan ke admin.');
    }

    public function approve(Sale $sale)
    {
        if (! auth()->user()->hasPermission('sales.approve')) {
            abort(403);
        }

        try {
            $this->sales->approve($sale);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Penjualan disetujui. Stok, kas/piutang, dan jurnal telah diposting.');
    }

    public function reject(Request $request, Sale $sale)
    {
        if (! auth()->user()->hasPermission('sales.approve')) {
            abort(403);
        }

        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        try {
            $this->sales->reject($sale, $request->rejection_reason);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Penjualan ditolak.');
    }

    private function authorizeView(Sale $sale): void
    {
        // if (! auth()->user()->isAdmin() && $sale->created_by !== auth()->id()) {
        //     abort(403);
        // }
    }
}
