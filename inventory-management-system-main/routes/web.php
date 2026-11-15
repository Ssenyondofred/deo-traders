<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\ApController;
use App\Http\Controllers\ArController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\FinancialStatementController;
use Illuminate\Support\Facades\Route;

// Jalur Login / Guest
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ==========================================
// KELOMPOK AKSES USER YANG SUDAH LOGIN (AUTH)
// ==========================================
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Inventory / Gudang (Staff bisa tambah stok & adjust)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/products/{product}', [InventoryController::class, 'show'])->name('show');
        
        // Form Tambah / Sesuikan Stok (Bisa diakses staff)
        Route::get('/adjustment/create', [StockAdjustmentController::class, 'create'])->name('adjustment.create');
        Route::post('/adjustment', [StockAdjustmentController::class, 'store'])->name('adjustment.store');
    });

    // Laporan Stok Gudang
    Route::get('/stock-report', [StockReportController::class, 'index'])->name('stock-report.index');

    // Modul Pembelian / Purchases (Staff bisa buat & edit, Admin yang approve)
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update');
        Route::post('/{purchase}/submit', [PurchaseController::class, 'submit'])->name('submit');
        
        // Kunci Approval Pembelian (Hanya Admin)
        Route::post('/{purchase}/approve', [PurchaseController::class, 'approve'])->middleware('admin')->name('approve');
        Route::post('/{purchase}/reject', [PurchaseController::class, 'reject'])->middleware('admin')->name('reject');
    });

    // Modul Produksi (Staff bisa buat, edit & ajukan)
    Route::prefix('production')->name('production.')->group(function () {
        Route::get('/', [ProductionController::class, 'index'])->name('index');
        Route::get('/create', [ProductionController::class, 'create'])->name('create');
        Route::post('/', [ProductionController::class, 'store'])->name('store');
        Route::get('/{production}', [ProductionController::class, 'show'])->name('show');
        Route::get('/{production}/edit', [ProductionController::class, 'edit'])->name('edit');
        Route::put('/{production}', [ProductionController::class, 'update'])->name('update');
        Route::post('/{production}/submit', [ProductionController::class, 'submit'])->name('submit');
        
        // Kunci Approval Produksi (Hanya Admin)
        Route::post('/{production}/approve', [ProductionController::class, 'approve'])->middleware('admin')->name('approve');
        Route::post('/{production}/reject', [ProductionController::class, 'reject'])->middleware('admin')->name('reject');
    });

    // Modul Penjualan / Sales (Staff bebas input penjualan baru)
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
        Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
        Route::post('/{sale}/submit', [SaleController::class, 'submit'])->name('submit');
        
        // Kunci Approval Penjualan (Hanya Admin)
        Route::post('/{sale}/approve', [SaleController::class, 'approve'])->middleware('admin')->name('approve');
        Route::post('/{sale}/reject', [SaleController::class, 'reject'])->middleware('admin')->name('reject');
    });

    // Modul Piutang Usaha / AR (Staff bisa buka detail & menginput penerimaan uang)
    Route::prefix('ar')->name('ar.')->group(function () {
        Route::get('/', [ArController::class, 'index'])->name('index');
        Route::get('/{ar}', [ArController::class, 'show'])->name('show');
        Route::post('/{ar}/pay', [ArController::class, 'payStore'])->name('pay');
    });

    // Modul Hutang Usaha / AP (Staff bisa catat pembayaran cicilan hutang perusahaan)
    Route::prefix('ap')->name('ap.')->group(function () {
        Route::get('/', [ApController::class, 'index'])->name('index');
        Route::get('/{ap}', [ApController::class, 'show'])->name('show');
        Route::post('/{ap}/pay', [ApController::class, 'payStore'])->name('pay');
    });

    // ==========================================
    // KELOMPOK SUPERVISI KEUANGAN (KHUSUS ADMIN)
    // ==========================================
    Route::middleware('admin')->group(function () {

        // Jurnal Akuntansi Manual
        Route::prefix('journals')->name('journals.')->group(function () {
            Route::get('/', [JournalController::class, 'index'])->name('index');
            Route::get('/create', [JournalController::class, 'create'])->name('create');
            Route::post('/', [JournalController::class, 'store'])->name('store');
            Route::get('/{journal}', [JournalController::class, 'show'])->name('show');
            Route::post('/{journal}/post', [JournalController::class, 'post'])->name('post');
            Route::post('/{journal}/void', [JournalController::class, 'void'])->name('void');
        });

        // Buku Besar & Neraca Saldo
        Route::prefix('ledger')->name('ledger.')->group(function () {
            Route::get('/', [LedgerController::class, 'index'])->name('index');
            Route::get('/trial-balance', [LedgerController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/{account}', [LedgerController::class, 'show'])->name('show');
        });

        // Laporan Keuangan Inti (Neraca, Laba Rugi, Arus Kas)
        Route::prefix('financial')->name('financial.')->group(function () {
            Route::get('/', [FinancialStatementController::class, 'index'])->name('index');
            Route::get('/income-statement', [FinancialStatementController::class, 'incomeStatement'])->name('income-statement');
            Route::get('/balance-sheet', [FinancialStatementController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/cash-flow', [FinancialStatementController::class, 'cashFlow'])->name('cash-flow');
        });

        // Log Aktivitas Audit Sistem
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        
        // Chart Dashboard Admin
        Route::get('/dashboard/chart-sales', [DashboardController::class, 'chartSales'])->name('dashboard.chart-sales');
    });

});