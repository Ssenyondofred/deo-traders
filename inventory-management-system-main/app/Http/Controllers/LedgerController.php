<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        return view('ledger.index', compact('accounts'));
    }

    public function show(Request $request, Account $account)
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to   ?? date('Y-m-d');

        // Saldo awal (sebelum periode)
        $openingDebit  = (float) JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                ->whereDate('entry_date', '<', $from))
            ->sum('debit');

        $openingCredit = (float) JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                ->whereDate('entry_date', '<', $from))
            ->sum('credit');

        $openingBalance = $account->normal_balance === 'debit'
            ? $openingDebit - $openingCredit
            : $openingCredit - $openingDebit;

        // Mutasi dalam periode
        $lines = JournalLine::with(['journalEntry'])
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                ->whereDate('entry_date', '>=', $from)
                ->whereDate('entry_date', '<=', $to))
            ->orderBy(
                \App\Models\JournalEntry::select('entry_date')
                    ->whereColumn('journal_entries.id', 'journal_lines.journal_entry_id'),
                'asc'
            )
            ->get();

        // Hitung running balance
        $runningBalance = $openingBalance;
        $rows = [];
        foreach ($lines as $line) {
            if ($account->normal_balance === 'debit') {
                $runningBalance += $line->debit - $line->credit;
            } else {
                $runningBalance += $line->credit - $line->debit;
            }
            $rows[] = [
                'date'        => $line->journalEntry->entry_date,
                'document_no' => $line->journalEntry->document_no,
                'description' => $line->description ?? $line->journalEntry->description,
                'debit'       => $line->debit,
                'credit'      => $line->credit,
                'balance'     => $runningBalance,
            ];
        }

        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('ledger.show', compact(
            'account', 'accounts', 'rows',
            'openingBalance', 'runningBalance',
            'from', 'to'
        ));
    }

    public function trialBalance(Request $request)
    {
        $date = $request->as_of ?? date('Y-m-d');

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($date) {
                $debit  = (float) JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                        ->whereDate('entry_date', '<=', $date))
                    ->sum('debit');

                $credit = (float) JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                        ->whereDate('entry_date', '<=', $date))
                    ->sum('credit');

                $balance = $account->normal_balance === 'debit'
                    ? $debit - $credit
                    : $credit - $debit;

                $account->tb_debit   = $debit;
                $account->tb_credit  = $credit;
                $account->tb_balance = $balance;
                return $account;
            })
            ->filter(fn($a) => $a->tb_debit > 0 || $a->tb_credit > 0);

        $totalDebit  = $accounts->sum('tb_debit');
        $totalCredit = $accounts->sum('tb_credit');

        return view('ledger.trial-balance', compact('accounts', 'totalDebit', 'totalCredit', 'date'));
    }
}