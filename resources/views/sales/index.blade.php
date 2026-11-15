@extends('layouts.admin')

@section('title', 'Sales')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Sales</h2>
        <p class="text-sm text-slate-500 mt-1">All sales invoices</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('sales.trash') }}" class="btn-primary-soft" style="background:#64748b;">Trash</a>
        <a href="{{ route('sales.create') }}" class="btn-primary-soft">+ Add Sale</a>
    </div>
</div>

<div class="panel p-5">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-3 py-2 text-left">Invoice</th>
                <th class="px-3 py-2 text-left">Customer</th>
                <th class="px-3 py-2 text-left">Date</th>
                <th class="px-3 py-2 text-right">Total</th>
                <th class="px-3 py-2 text-right">Paid</th>
                <th class="px-3 py-2 text-right">Balance</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr class="border-b border-slate-100">
                    <td class="px-3 py-2"><a href="{{ route('sales.show', $sale) }}" class="text-blue-600">{{ $sale->invoice_number }}</a></td>
                    <td class="px-3 py-2">{{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in') }}</td>
                    <td class="px-3 py-2">{{ optional($sale->created_at)->timezone('Africa/Kampala')->format('d M Y') }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($sale->total, 0) }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($sale->paid, 0) }}</td>
                    <td class="px-3 py-2 text-right font-semibold text-red-600">{{ number_format($sale->balance, 0) }}</td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 text-xs">Invoice</a>
                        <a href="{{ route('sales.edit', $sale) }}" class="text-slate-600 text-xs ml-2">Edit</a>
                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-xs ml-2" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">No sales yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $sales->links() }}</div>
</div>
@endsection