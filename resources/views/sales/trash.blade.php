@extends('layouts.admin')

@section('title', 'Trash')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Trash</h2>
        <p class="text-sm text-slate-500 mt-1">Deleted invoices (auto-deleted after 90 days)</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn-primary-soft">Back to Sales</a>
</div>

<div class="panel p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Invoice</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Customer</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Deleted At</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Total</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2">{{ $sale->invoice_number }}</td>
                        <td class="px-3 py-2">{{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in') }}</td>
                        <td class="px-3 py-2">{{ $sale->deleted_at->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($sale->total, 0) }}</td>
                        <td class="px-3 py-2 text-right">
                            <form action="{{ route('sales.restore', $sale->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-emerald-600 text-xs font-medium">Restore</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">Trash is empty.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $sales->links() }}</div>
</div>
@endsection