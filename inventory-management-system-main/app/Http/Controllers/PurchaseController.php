<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\PurchaseRequest;
use App\Models\CashBankAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchases)
    {
    }

    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'creator'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if (! auth()->user()->isAdmin()) {
            // $query->where('created_by', auth()->id());
        }

        $purchases = $query->paginate(15)->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.form', [
            'purchase' => null,
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->where('track_stock', true)->orderBy('name')->get(),
            'cashAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(PurchaseRequest $request)
    {
        try {
            $purchase = $this->purchases->create(
                $request->only([
                    'purchase_date', 'supplier_id', 'payment_method',
                    'cash_bank_account_id', 'discount', 'is_taxable',
                    'tax_percent', 'due_date', 'notes',
                ]),
                $request->items,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = 'Pembelian berhasil disimpan sebagai draft.';

        if ($request->boolean('submit_after_save')) {
            $this->purchases->submit($purchase);
            $message = 'Pembelian disimpan dan diajukan untuk approval admin.';
        }

        return redirect()->route('purchases.show', $purchase)->with('success', $message);
    }

    public function show(Purchase $purchase)
    {
        $this->authorizeView($purchase);

        $purchase->load([
            'items.product',
            'supplier',
            'cashBankAccount',
            'creator',
            'approver',
            'apDocument',
            'journalEntry.lines.account',
        ]);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $this->authorizeView($purchase);

        if (! $purchase->isEditable()) {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Pembelian tidak dapat diedit pada status ini.');
        }

        $purchase->load('items');

        return view('purchases.form', [
            'purchase' => $purchase,
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->where('track_stock', true)->orderBy('name')->get(),
            'cashAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        $this->authorizeView($purchase);

        try {
            $this->purchases->update(
                $purchase,
                $request->only([
                    'purchase_date', 'supplier_id', 'payment_method',
                    'cash_bank_account_id', 'discount', 'is_taxable',
                    'tax_percent', 'due_date', 'notes',
                ]),
                $request->items,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->boolean('submit_after_save')) {
            $this->purchases->submit($purchase);
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil diperbarui.');
    }

    public function submit(Purchase $purchase)
    {
        $this->authorizeView($purchase);

        try {
            $this->purchases->submit($purchase);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembelian diajukan ke admin untuk approval.');
    }

    public function approve(Purchase $purchase)
    {
        if (! auth()->user()->hasPermission('purchases.approve')) {
            abort(403);
        }

        try {
            $this->purchases->approve($purchase);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembelian disetujui. Stok, jurnal, dan hutang/kas telah diposting.');
    }

    public function reject(Request $request, Purchase $purchase)
    {
        if (! auth()->user()->hasPermission('purchases.approve')) {
            abort(403);
        }

        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        try {
            $this->purchases->reject($purchase, $request->rejection_reason);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembelian ditolak.');
    }

    private function authorizeView(Purchase $purchase): void
    {
        if (! auth()->user()->isAdmin() && $purchase->created_by !== auth()->id()) {
            abort(403);
        }
    }
}
