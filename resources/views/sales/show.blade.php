@extends('layouts.admin')

@section('title', 'Invoice ' . $sale->invoice_number)

@section('content')
<div class="mb-6 flex items-center justify-between no-print">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Invoice</h2>
        <p class="text-sm text-slate-500">{{ $sale->invoice_number }}</p>
    </div>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn-primary-soft">Print</button>
        <a href="{{ route('sales.edit', $sale) }}" class="btn-primary-soft" style="background:#64748b;">Edit</a>
        <a href="{{ route('sales.index') }}" class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg">Back</a>
    </div>
</div>

<div class="invoice-wrapper bg-white max-w-2xl mx-auto border border-slate-200 rounded-lg overflow-hidden" id="invoice-content">
    <div class="h-1.5 bg-slate-900"></div>

    <div class="px-10 py-8">
        {{-- Centered Header --}}
        <div class="text-center pb-6 border-b border-slate-200">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Deo Traders</h1>
            <p class="text-sm text-slate-500 mt-0.5">Sales Invoice</p>
            <p class="text-xs text-slate-400 mt-2">0759531168 &middot; 0772991035 &middot; 0758554160</p>
            <p class="text-xs text-slate-400 mt-1">{{ $sale->created_at->format('d F Y') }} &middot; {{ $sale->created_at->format('h:i A') }}</p>
        </div>

        {{-- Invoice Info --}}
        <div class="flex justify-between mt-6 text-sm">
            <div>
                <div class="text-xs font-medium text-slate-400 uppercase tracking-wider">Bill To</div>
                <div class="mt-1 font-medium text-slate-900">{{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in') }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs font-medium text-slate-400 uppercase tracking-wider">Invoice</div>
                <div class="mt-1 font-medium text-slate-900">{{ $sale->invoice_number }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ ucfirst($sale->payment_status ?? $sale->status) }}</div>
            </div>
        </div>

        {{-- Items --}}
        <table class="w-full mt-8 text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wide">
                    <th class="pb-3 text-left font-medium w-10">#</th>
                    <th class="pb-3 text-left font-medium">Description</th>
                    <th class="pb-3 text-right font-medium pr-4">Rate</th>
                    <th class="pb-3 text-right font-medium pr-4">Qty</th>
                    <th class="pb-3 text-right font-medium">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sale->items as $index => $item)
                    <tr class="border-b border-slate-100">
                        <td class="py-3 text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-3 text-slate-900">{{ $item->product_name ?? '—' }}</td>
                        <td class="py-3 text-right text-slate-700 pr-4">UGX {{ number_format($item->unit_price, 0) }}</td>
                        <td class="py-3 text-right text-slate-700 pr-4">{{ number_format($item->quantity, 0) }}</td>
                        <td class="py-3 text-right font-medium text-slate-900">UGX {{ number_format($item->line_total, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">No line items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="mt-6 flex justify-end">
            <div class="w-72 text-sm">
                <div class="flex justify-between py-1.5 text-slate-600">
                    <span>Subtotal</span>
                    <span>UGX {{ number_format($sale->subtotal ?? $sale->total, 0) }}</span>
                </div>
                <div class="flex justify-between py-1.5 text-slate-600">
                    <span>Discount</span>
                    <span>UGX {{ number_format($sale->discount ?? 0, 0) }}</span>
                </div>
                <div class="flex justify-between py-1.5 text-slate-600">
                    <span>Tax</span>
                    <span>UGX {{ number_format($sale->tax ?? 0, 0) }}</span>
                </div>
                <div class="flex justify-between py-2.5 border-t border-slate-200 font-semibold text-slate-900">
                    <span>Total</span>
                    <span>UGX {{ number_format($sale->total, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Paid & Balance --}}
        <div class="mt-4 border-t border-slate-200 pt-4">
            <div class="flex justify-end">
                <div class="w-72 text-sm">
                    <div class="flex justify-between py-2">
                        <span class="text-slate-600">Amount Paid</span>
                        <span class="font-medium text-emerald-600">UGX {{ number_format($sale->paid, 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t border-slate-200">
                        <span class="font-semibold text-slate-900">Balance Due</span>
                        <span class="font-semibold text-lg" style="color:#b45309;">UGX {{ number_format($sale->balance, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-10 pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
            Thank you for your business.
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .invoice-wrapper, .invoice-wrapper * { visibility: visible; }
    .invoice-wrapper {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100%;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }
    .invoice-wrapper > div:last-child {
        padding: 20px 30px !important;
    }
    .no-print { display: none !important; }
    @page { margin: 15mm 20mm; }
}
</style>
@endsection