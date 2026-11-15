@extends('layouts.admin')

@section('title', 'Purchases')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Purchases</h2>
        <p class="text-sm text-slate-500 mt-1">All purchase orders</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn-primary-soft">+ Add Purchase</a>
</div>

<div class="panel p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Number</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Supplier</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Total</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Paid</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Balance</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2">{{ $purchase->purchase_number }}</td>
                        <td class="px-3 py-2">{{ $purchase->supplier->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M Y') : $purchase->created_at->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($purchase->total, 0) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($purchase->paid, 0) }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-red-600">{{ number_format($purchase->balance, 0) }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('purchases.edit', $purchase) }}" class="text-slate-600 text-xs">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">No purchases yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $purchases->links() }}</div>
</div>
@endsection