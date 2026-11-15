<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        $debtors = \App\Models\Sale::where('balance', '>', 0)
            ->with('customer')
            ->orderByDesc('balance')
            ->take(10)
            ->get();

        return view('dashboard', [
            'todaySales'      => \App\Models\Sale::whereDate('created_at', today())->sum('total'),
            'monthSales'      => \App\Models\Sale::whereMonth('created_at', now()->month)->sum('total'),
            'totalDebt'       => \App\Models\Sale::sum('balance'),
            'outstanding'     => \App\Models\Purchase::sum('balance'),
            'todayPurchases'  => \App\Models\Purchase::whereDate('created_at', today())->sum('total'),
            'monthPurchases'  => \App\Models\Purchase::whereMonth('created_at', now()->month)->sum('total'),
            'lowStock'        => 0,
            'totalProducts'   => \App\Models\Product::count(),
            'totalCustomers'  => \App\Models\Customer::count(),
            'totalSuppliers'  => \App\Models\Supplier::count(),
            'totalCategories' => \App\Models\Category::count(),
            'debtors'         => $debtors,
        ]);
    })->name('dashboard');

    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::resource('brands', \App\Http\Controllers\BrandController::class);
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::resource('products', \App\Http\Controllers\ProductController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);

    Route::get('/sales/trash', [\App\Http\Controllers\SaleController::class, 'trash'])->name('sales.trash');
    Route::post('/sales/{id}/restore', [\App\Http\Controllers\SaleController::class, 'restore'])->name('sales.restore');
    Route::delete('/sales/{id}/force', [\App\Http\Controllers\SaleController::class, 'forceDelete'])->name('sales.forceDelete');

    Route::resource('sales', \App\Http\Controllers\SaleController::class);
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);

    Route::get('/purchases/daily', fn() => 'Daily Receipt coming soon')->name('purchases.daily');
    Route::get('/users', fn() => 'Users coming soon')->name('users.index');

    Route::get('/reports', function () {
        $debtors = \App\Models\Sale::where('balance', '>', 0)
            ->with('customer')
            ->orderByDesc('balance')
            ->take(10)
            ->get();

        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = \App\Models\Sale::whereDate('created_at', $date)->sum('total');
        }

        return view('reports.index', [
            'todaySales'      => \App\Models\Sale::whereDate('created_at', today())->sum('total'),
            'monthSales'      => \App\Models\Sale::whereMonth('created_at', now()->month)->sum('total'),
            'totalDebt'       => \App\Models\Sale::sum('balance'),
            'todayPurchases'  => \App\Models\Purchase::whereDate('created_at', today())->sum('total'),
            'monthPurchases'  => \App\Models\Purchase::whereMonth('created_at', now()->month)->sum('total'),
            'totalCreditors'  => \App\Models\Purchase::sum('balance'),
            'debtors'         => $debtors,
            'chartLabels'     => $labels,
            'chartData'       => $data,
        ]);
    })->name('reports.index');
});

require __DIR__.'/auth.php';