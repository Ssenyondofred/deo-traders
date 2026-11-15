<?php

namespace App\Http\Controllers;

use App\Models\ArDocument;
use App\Models\CashBankAccount;
use App\Services\ArPaymentService;
use Illuminate\Http\Request;

class ArController extends Controller
{
    public function __construct(private ArPaymentService $service) {}

    public function index(Request $request)
    {
        $query = ArDocument::with('customer')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->whereHas('customer',
                fn($c) => $c->where('name', 'like', '%'.$request->search.'%')))
            ->orderByRaw("FIELD(status,'open','partial','paid')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        $summary = [
            'total_open'    => ArDocument::where('status', 'open')->sum('balance_amount'),
            'total_partial' => ArDocument::where('status', 'partial')->sum('balance_amount'),
            'total_paid'    => ArDocument::where('status', 'paid')->sum('original_amount'),
        ];

        return view('ar.index', compact('query', 'summary'));
    }

    public function show(ArDocument $ar)
    {
        $ar->load(['customer', 'payments.cashBankAccount', 'payments.journalEntry']);
        $cashBanks = CashBankAccount::where('is_active', true)->get();
        return view('ar.show', compact('ar', 'cashBanks'));
    }

    public function payStore(Request $request, ArDocument $ar)
    {
        $request->validate([
            'payment_date'        => 'required|date',
            'amount'              => 'required|numeric|min:1|max:'.$ar->balance_amount,
            'cash_bank_account_id'=> 'required|exists:cash_bank_accounts,id',
            'notes'               => 'nullable|string|max:255',
        ]);

        try {
            $this->service->pay($ar, $request->all());
            return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}