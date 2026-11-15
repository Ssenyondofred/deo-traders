<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ArDocument;
use App\Models\ApDocument;
use App\Models\CashBankAccount;
use App\Models\JournalLine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->staffDashboard();
    }

    // -------------------------------------------------------
    // ADMIN DASHBOARD
    // -------------------------------------------------------
    private function adminDashboard()
    {
        $today     = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        // ── Ringkasan KAS ──
        $cashBalance = CashBankAccount::where('is_active', true)->sum('current_balance');

        // ── Piutang & Hutang ──
        $totalAR = ArDocument::whereIn('status', ['open','partial'])->sum('balance_amount');
        $totalAP = ApDocument::whereIn('status', ['open','partial'])->sum('balance_amount');

        // ── Stok kritis (qty < 10) ──
        $lowStock = \App\Models\StockBalance::with('product')
            ->where('qty_on_hand', '<', 10)
            ->where('qty_on_hand', '>', 0)
            ->count();

        // ── Penjualan bulan ini ──
        $salesThisMonth = Sale::where('status', 'posted')
            ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $thisMonth)
            ->sum('grand_total');

        // ── Pembelian bulan ini ──
        $purchaseThisMonth = Purchase::where('status', 'posted')
            ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $thisMonth)
            ->sum('grand_total');

        // ── Pending approval ──
        $pendingSales     = Sale::where('status', 'pending_approval')->count();
        $pendingPurchases = Purchase::where('status', 'pending_approval')->count();

        // ── Chart 1: Penjualan vs Pembelian 6 bulan terakhir (Bar) ──
        $months     = collect();
        $salesData  = collect();
        $purchData  = collect();

        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->translatedFormat('M Y');
            $months->push($label);

            $salesData->push((float) Sale::where('status', 'posted')
                ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $m)
                ->sum('grand_total'));

            $purchData->push((float) Purchase::where('status', 'posted')
                ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $m)
                ->sum('grand_total'));
        }

        // ── Chart 2: Laba Kotor 6 bulan (Line) ──
        $profitData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m    = now()->subMonths($i)->format('Y-m');
            $from = now()->subMonths($i)->startOfMonth()->toDateString();
            $to   = now()->subMonths($i)->endOfMonth()->toDateString();

            $rev = (float) JournalLine::whereHas('account', fn($q) => $q->where('type','revenue'))
                ->whereHas('journalEntry', fn($q) => $q->where('status','posted')
                    ->whereDate('entry_date','>=',$from)->whereDate('entry_date','<=',$to))
                ->sum('credit');

            $cogs = (float) JournalLine::whereHas('account', fn($q) => $q->where('type','cogs'))
                ->whereHas('journalEntry', fn($q) => $q->where('status','posted')
                    ->whereDate('entry_date','>=',$from)->whereDate('entry_date','<=',$to))
                ->sum('debit');

            $profitData->push(round($rev - $cogs, 0));
        }

        // ── Chart 3: Arus Kas harian 30 hari terakhir (Line) ──
        $cashDates  = collect();
        $cashIn     = collect();
        $cashOut    = collect();

        $cashAccountIds = CashBankAccount::where('is_active', true)->pluck('account_id');

        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $cashDates->push(now()->subDays($i)->format('d/m'));

            $in = (float) JournalLine::whereIn('account_id', $cashAccountIds)
                ->whereHas('journalEntry', fn($q) => $q->where('status','posted')
                    ->whereDate('entry_date', $d))
                ->sum('debit');

            $out = (float) JournalLine::whereIn('account_id', $cashAccountIds)
                ->whereHas('journalEntry', fn($q) => $q->where('status','posted')
                    ->whereDate('entry_date', $d))
                ->sum('credit');

            $cashIn->push(round($in, 0));
            $cashOut->push(round($out, 0));
        }

        // ── Transaksi harian terbaru ──
        $recentTransactions = collect()
            ->merge(Sale::with('customer')->where('status','posted')
                ->latest('created_at')->take(5)->get()
                ->map(fn($r) => ['type'=>'Penjualan','no'=>$r->document_no,
                    'partner'=>$r->customer->name,'amount'=>$r->grand_total,
                    'date'=>$r->created_at]))
            ->merge(Purchase::with('supplier')->where('status','posted')
                ->latest('created_at')->take(5)->get()
                ->map(fn($r) => ['type'=>'Pembelian','no'=>$r->document_no,
                    'partner'=>$r->supplier->name,'amount'=>$r->grand_total,
                    'date'=>$r->created_at]))
            ->sortByDesc('date')->take(10)->values();

        return view('dashboard.admin', compact(
            'cashBalance','totalAR','totalAP','lowStock',
            'salesThisMonth','purchaseThisMonth',
            'pendingSales','pendingPurchases',
            'months','salesData','purchData','profitData',
            'cashDates','cashIn','cashOut',
            'recentTransactions'
        ));
    }

    // -------------------------------------------------------
    // STAFF DASHBOARD
    // -------------------------------------------------------
    private function staffDashboard()
    {
        $thisMonth = now()->format('Y-m');

        $mySales = Sale::where('created_by', auth()->id())
            ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $thisMonth)
            ->count();

        $myPurchases = Purchase::where('created_by', auth()->id())
            ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $thisMonth)
            ->count();

        $pendingSales     = Sale::where('created_by', auth()->id())
            ->where('status', 'pending_approval')->count();
        $pendingPurchases = Purchase::where('created_by', auth()->id())
            ->where('status', 'pending_approval')->count();

        $stockItems = \App\Models\StockBalance::with('product')
            ->orderBy('qty_on_hand')
            ->take(10)->get();

        return view('dashboard.staff', compact(
            'mySales','myPurchases',
            'pendingSales','pendingPurchases',
            'stockItems'
        ));
    }

    // -------------------------------------------------------
    // AJAX: data chart realtime (opsional)
    // -------------------------------------------------------
    public function chartSales(Request $request)
    {
        $months = collect();
        $data   = collect();
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $months->push(now()->subMonths($i)->format('M Y'));
            $data->push((float) Sale::where('status','posted')
                ->where(DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), $m)
                ->sum('grand_total'));
        }
        return response()->json(['labels' => $months, 'data' => $data]);
    }
}