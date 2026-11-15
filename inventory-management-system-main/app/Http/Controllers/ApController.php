<?php

namespace App\Http\Controllers;

use App\Models\ApDocument;
use App\Models\CashBankAccount;
use App\Services\ApPaymentService;
use Illuminate\Http\Request;

class ApController extends Controller
{
    public function __construct(private ApPaymentService $service) {}

    public function index(Request $request)
    {
        $query = ApDocument::with('supplier')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->whereHas('supplier',
                fn($s) => $s->where('name', 'like', '%'.$request->search.'%')))
            ->orderByRaw("FIELD(status,'open','partial','paid')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        $summary = [
            'total_open'    => ApDocument::where('status', 'open')->sum('balance_amount'),
            'total_partial' => ApDocument::where('status', 'partial')->sum('balance_amount'),
            'total_paid'    => ApDocument::where('status', 'paid')->sum('original_amount'),
        ];

        return view('ap.index', compact('query', 'summary'));
    }

    public function show(ApDocument $ap)
    {
        $ap->load(['supplier', 'payments.cashBankAccount', 'payments.journalEntry']);
        $cashBanks = CashBankAccount::where('is_active', true)->get();
        return view('ap.show', compact('ap', 'cashBanks'));
    }

    public function payStore(Request $request, ApDocument $ap)
    {
        $request->validate([
            'payment_date'        => 'required|date',
            'amount'              => 'required|numeric|min:1|max:'.$ap->balance_amount,
            'cash_bank_account_id'=> 'required|exists:cash_bank_accounts,id',
            'notes'               => 'nullable|string|max:255',
        ]);

        try {
            $this->service->pay($ap, $request->all());
            return back()->with('success', 'Pembayaran hutang berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}