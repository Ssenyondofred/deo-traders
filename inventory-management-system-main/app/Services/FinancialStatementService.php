<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Support\Collection;

class FinancialStatementService
{
    /**
     * Ambil saldo akun per tanggal (kumulatif dari awal)
     */
    private function getAccountBalances(string $toDate, string $fromDate = null): Collection
    {
        return Account::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($fromDate, $toDate) {
                $query = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn($q) => $q
                        ->where('status', 'posted')
                        ->whereDate('entry_date', '<=', $toDate)
                    );

                $debit  = (float) $query->sum('debit');
                $credit = (float) $query->sum('credit');

                $account->balance = $account->normal_balance === 'debit'
                    ? $debit - $credit
                    : $credit - $debit;

                // Untuk laporan periode (L/R): hanya dalam range
                if ($fromDate) {
                    $periodQuery = JournalLine::where('account_id', $account->id)
                        ->whereHas('journalEntry', fn($q) => $q
                            ->where('status', 'posted')
                            ->whereDate('entry_date', '>=', $fromDate)
                            ->whereDate('entry_date', '<=', $toDate)
                        );
                    $pd = (float) $periodQuery->sum('debit');
                    $pc = (float) $periodQuery->sum('credit');
                    $account->period_balance = $account->normal_balance === 'debit'
                        ? $pd - $pc
                        : $pc - $pd;
                }

                return $account;
            });
    }

    /**
     * LABA RUGI
     */
    public function incomeStatement(string $from, string $to): array
    {
        $accounts = $this->getAccountBalances($to, $from);

        $revenue   = $accounts->where('account_type', 'revenue')
            ->filter(fn($a) => $a->period_balance != 0);
        $cogs      = $accounts->where('account_type', 'cogs')
            ->filter(fn($a) => $a->period_balance != 0);
        $expenses  = $accounts->where('account_type', 'expense')
            ->filter(fn($a) => $a->period_balance != 0);
        $otherIncome = $accounts->where('account_type', 'other_income')
            ->filter(fn($a) => $a->period_balance != 0);
        $otherExp  = $accounts->where('account_type', 'other_expense')
            ->filter(fn($a) => $a->period_balance != 0);

        $totalRevenue    = $revenue->sum('period_balance');
        $totalCogs       = $cogs->sum('period_balance');
        $grossProfit     = $totalRevenue - $totalCogs;
        $totalExpenses   = $expenses->sum('period_balance');
        $operatingProfit = $grossProfit - $totalExpenses;
        $totalOtherInc   = $otherIncome->sum('period_balance');
        $totalOtherExp   = $otherExp->sum('period_balance');
        $netProfit       = $operatingProfit + $totalOtherInc - $totalOtherExp;

        return compact(
            'revenue', 'cogs', 'expenses', 'otherIncome', 'otherExp',
            'totalRevenue', 'totalCogs', 'grossProfit',
            'totalExpenses', 'operatingProfit',
            'totalOtherInc', 'totalOtherExp', 'netProfit'
        );
    }

    /**
     * NERACA (BALANCE SHEET)
     */
    public function balanceSheet(string $asOf): array
    {
        $accounts = $this->getAccountBalances($asOf);

        $currentAssets    = $accounts->where('account_type', 'current_asset')
            ->filter(fn($a) => $a->balance != 0);
        $fixedAssets      = $accounts->where('account_type', 'fixed_asset')
            ->filter(fn($a) => $a->balance != 0);
        $currentLiab      = $accounts->where('account_type', 'current_liability')
            ->filter(fn($a) => $a->balance != 0);
        $longTermLiab     = $accounts->where('account_type', 'long_term_liability')
            ->filter(fn($a) => $a->balance != 0);
        $equity           = $accounts->where('account_type', 'equity')
            ->filter(fn($a) => $a->balance != 0);

        $totalRevenue = (float) JournalLine::whereHas('journalEntry', fn($q) =>
            $q->where('status', 'posted')->whereDate('entry_date', '<=', $asOf))
            ->whereHas('account', fn($q) => $q->whereIn('account_type', ['revenue', 'other_income'])) // ◄── Diubah ke account_type
            ->sum('credit');

        $totalExpense = (float) JournalLine::whereHas('journalEntry', fn($q) =>
            $q->where('status', 'posted')->whereDate('entry_date', '<=', $asOf))
            ->whereHas('account', fn($q) => $q->whereIn('account_type', ['cogs','expense','other_expense'])) // ◄── Diubah ke account_type
            ->sum('debit');

        $retainedEarnings = $totalRevenue - $totalExpense;

        $totalCurrentAssets  = $currentAssets->sum('balance');
        $totalFixedAssets    = $fixedAssets->sum('balance');
        $totalAssets         = $totalCurrentAssets + $totalFixedAssets;
        $totalCurrentLiab    = $currentLiab->sum('balance');
        $totalLongTermLiab   = $longTermLiab->sum('balance');
        $totalLiabilities    = $totalCurrentLiab + $totalLongTermLiab;
        $totalEquity         = $equity->sum('balance') + $retainedEarnings;
        $totalLiabEquity     = $totalLiabilities + $totalEquity;

        return compact(
            'currentAssets', 'fixedAssets',
            'currentLiab', 'longTermLiab', 'equity',
            'totalCurrentAssets', 'totalFixedAssets', 'totalAssets',
            'totalCurrentLiab', 'totalLongTermLiab', 'totalLiabilities',
            'retainedEarnings', 'totalEquity', 'totalLiabEquity'
        );
    }

    /**
     * ARUS KAS (CASH FLOW — indirect method sederhana)
     */
    public function cashFlow(string $from, string $to): array
    {
        // Kas & bank: akun type current_asset dengan kode 1001/1002
        $cashAccounts = Account::where('is_active', true)
            ->where('account_type', 'current_asset')
            ->where(fn($q) => $q->where('code', 'like', '1001%')
                ->orWhere('code', 'like', '1002%'))
            ->pluck('id');

        // Saldo kas awal (sebelum $from)
        $openingCash = $this->getCashBalance($cashAccounts, null, date('Y-m-d', strtotime($from.' -1 day')));

        // Saldo kas akhir
        $closingCash = $this->getCashBalance($cashAccounts, null, $to);

        // Operasional: dari penjualan & pembelian (Menggunakan kolom reference_type yang benar)
        $cashFromSales = (float) JournalLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->where('reference_type', 'like', '%Sale%') // ◄── SEBELUMNYA 'source_type'
                ->whereDate('entry_date', '>=', $from)
                ->whereDate('entry_date', '<=', $to))
            ->sum('debit');

        $cashForPurchase = (float) JournalLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->where('reference_type', 'like', '%Purchase%') // ◄── SEBELUMNYA 'source_type'
                ->whereDate('entry_date', '>=', $from)
                ->whereDate('entry_date', '<=', $to))
            ->sum('credit');

        $cashForExpenses = (float) JournalLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->where('reference_type', 'like', '%Expense%') // ◄── SEBELUMNYA 'source_type'
                ->whereDate('entry_date', '>=', $from)
                ->whereDate('entry_date', '<=', $to))
            ->sum('credit');

        // Penerimaan piutang
        $cashFromAR = (float) JournalLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->where('reference_type', 'like', '%ArPayment%') // ◄── SEBELUMNYA 'source_type'
                ->whereDate('entry_date', '>=', $from)
                ->whereDate('entry_date', '<=', $to))
            ->sum('debit');

        // Pembayaran hutang
        $cashForAP = (float) JournalLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->where('reference_type', 'like', '%ApPayment%') // ◄── SEBELUMNYA 'source_type'
                ->whereDate('entry_date', '>=', $from)
                ->whereDate('entry_date', '<=', $to))
            ->sum('credit');

        $operatingCash = $cashFromSales + $cashFromAR - $cashForPurchase - $cashForExpenses - $cashForAP;
        $netCashChange = $closingCash - $openingCash;

        return compact(
            'openingCash', 'closingCash', 'netCashChange',
            'cashFromSales', 'cashFromAR',
            'cashForPurchase', 'cashForExpenses', 'cashForAP',
            'operatingCash'
        );
    }
}