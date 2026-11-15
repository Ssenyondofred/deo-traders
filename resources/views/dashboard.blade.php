@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Dashboard</h2>
    <p class="text-sm text-slate-500 mt-1">Overview of sales, purchases, stock and people</p>
</div>

{{-- KPI Cards Row 1 --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="kpi" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
        <div class="label">Today's Sales</div>
        <div class="value">UGX {{ number_format($todaySales ?? 0, 0) }}</div>
        <div class="hint">All sales today</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
        <div class="label">This Month Sales</div>
        <div class="value">UGX {{ number_format($monthSales ?? 0, 0) }}</div>
        <div class="hint">{{ now()->format('F Y') }}</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
        <div class="label">Debtors (Sales)</div>
        <div class="value">UGX {{ number_format($totalDebt ?? 0, 0) }}</div>
        <div class="hint">Unpaid customer balances</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
        <div class="label">Creditors (Purchases)</div>
        <div class="value">UGX {{ number_format($outstanding ?? 0, 0) }}</div>
        <div class="hint">Unpaid supplier balances</div>
    </div>
</div>

{{-- KPI Cards Row 2 --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="kpi" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
        <div class="label">Today's Purchases</div>
        <div class="value">UGX {{ number_format($todayPurchases ?? 0, 0) }}</div>
        <div class="hint">All purchases today</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#14b8a6,#0f766e);">
        <div class="label">This Month Purchases</div>
        <div class="value">UGX {{ number_format($monthPurchases ?? 0, 0) }}</div>
        <div class="hint">{{ now()->format('F Y') }}</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#10b981,#059669);">
        <div class="label">Low Stock</div>
        <div class="value">{{ $lowStock ?? 0 }}</div>
        <div class="hint">Items with qty ≤ 10</div>
    </div>
    <div class="kpi" style="background:linear-gradient(135deg,#64748b,#334155);">
        <div class="label">Products</div>
        <div class="value">{{ $totalProducts ?? 0 }}</div>
        <div class="hint">In catalog</div>
    </div>
</div>

{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Products</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalProducts ?? 0 }}</div>
    </div>
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Customers</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalCustomers ?? 0 }}</div>
    </div>
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Suppliers</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSuppliers ?? 0 }}</div>
    </div>
    <div class="panel p-4">
        <div class="text-xs text-slate-500 font-medium">Categories</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalCategories ?? 0 }}</div>
    </div>
</div>

{{-- Outstanding Debtors --}}
<div class="panel p-5 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-slate-800">Outstanding Debtors</h3>
        <span class="text-sm font-bold text-red-600">
            Total: UGX {{ number_format($totalDebt ?? 0, 0) }}
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Invoice</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Customer</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Phone</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Total</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Paid</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($debtors ?? []) as $d)
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2">
                            <a href="{{ route('sales.edit', $d) }}" class="text-blue-600 font-medium hover:underline">
                                {{ $d->invoice_number ?? '—' }}
                            </a>
                        </td>
                        <td class="px-3 py-2">{{ $d->customer_name ?: ($d->customer->name ?? 'Walk-in') }}</td>
                        <td class="px-3 py-2">{{ $d->customer->phone ?? '—' }}</td>
                        <td class="px-3 py-2">{{ optional($d->created_at)->timezone('Africa/Kampala')->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($d->total ?? 0, 0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($d->paid ?? 0, 0) }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-red-600">{{ number_format($d->balance ?? 0, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">
                            No outstanding debtors.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Quick Actions --}}
<div class="panel p-5">
    <div class="font-semibold text-slate-800 mb-3">Quick actions</div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('sales.create') }}" class="btn-primary-soft" style="background:#2563eb;">+ Sale</a>
        <a href="{{ route('products.create') }}" class="btn-primary-soft">+ Product</a>
        <a href="{{ route('purchases.create') }}" class="btn-primary-soft" style="background:#0f766e;">+ Purchase</a>
        <a href="{{ route('customers.create') }}" class="btn-primary-soft" style="background:#7c3aed;">+ Customer</a>
        <a href="{{ route('suppliers.create') }}" class="btn-primary-soft" style="background:#b45309;">+ Supplier</a>
        <a href="{{ route('reports.index') }}" class="btn-primary-soft" style="background:#334155;">Reports</a>
    </div>
</div>
@endsection