<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function __construct(private JournalService $service) {}

    public function index(Request $request)
    {
        $entries = JournalEntry::with('createdBy')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type,   fn($q) => $q->where('entry_type', $request->type))
            ->when($request->from,   fn($q) => $q->whereDate('entry_date', '>=', $request->from))
            ->when($request->to,     fn($q) => $q->whereDate('entry_date', '<=', $request->to))
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('document_no', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            }))
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)->withQueryString();

        return view('journals.index', compact('entries'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return view('journals.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'          => 'required|date',
            'description'         => 'required|string|max:255',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:2',
            'lines.*.account_id'  => 'required|exists:accounts,id',
            'lines.*.debit'       => 'nullable|numeric|min:0',
            'lines.*.credit'      => 'nullable|numeric|min:0',
        ]);

        try {
            $entry = $this->service->createManual($request->all());
            return redirect()->route('journals.show', $entry)
                ->with('success', 'Jurnal berhasil dibuat. Silakan post jika sudah benar.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(JournalEntry $journal)
    {
        $journal->load(['lines.account', 'createdBy', 'postedBy']);
        return view('journals.show', compact('journal'));
    }

    public function post(JournalEntry $journal)
    {
        try {
            $this->service->post($journal);
            return back()->with('success', 'Jurnal berhasil di-post.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function void(Request $request, JournalEntry $journal)
    {
        $request->validate(['reason' => 'required|string|max:255']);
        try {
            $this->service->void($journal, $request->reason);
            return back()->with('success', 'Jurnal berhasil di-void. Jurnal pembalik telah dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}