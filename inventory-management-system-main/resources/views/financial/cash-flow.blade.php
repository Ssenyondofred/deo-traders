@extends('layouts.app')
@section('title', 'Laporan Arus Kas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Cash Flow Report</h5>
    <div>
        <a href="{{ route('financial.index') }}" class="btn btn-sm btn-secondary me-1">← Back</a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-dark">🖨 Print</button>
    </div>
</div>

<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Show</button>
    </div>
</form>

<div class="card shadow-sm" id="printArea">
    <div class="card-body">
        <div class="text-center mb-3">
            <h6 class="fw-bold mb-0">CASH FLOW REPORT</h6>
            <div class="text-muted small">
                Period {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} to
                {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            </div>
        </div>

        <table class="table table-sm table-borderless">
            {{-- Saldo awal --}}
            <tr class="fw-bold table-light">
                <td>Saldo Kas Awal</td>
                <td class="text-end">{{ number_format($openingCash,0,',','.') }}</td>
            </tr>

            <tr><td colspan="2" class="py-1"></td></tr>

            {{-- Aktivitas Operasional --}}
            <tr class="fw-bold table-primary">
                <td colspan="2">OPERATING ACTIVITIES</td>
            </tr>

            <tr>
                <td class="ps-4">Cash Inflows from Sales</td>
                <td class="text-end text-success">+ {{ number_format($cashFromSales,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4">Cash Inflows from Accounts Receivable Collection</td>
                <td class="text-end text-success">+ {{ number_format($cashFromAR,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4">Cash Outflows for Purchase of Inventory</td>
                <td class="text-end text-danger">- {{ number_format($cashForPurchase,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4">Cash Outflows for Operating Expenses</td>
                <td class="text-end text-danger">- {{ number_format($cashForExpenses,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4">Cash Outflows for Accounts Payable Payment</td>
                <td class="text-end text-danger">- {{ number_format($cashForAP,0,',','.') }}</td>
            </tr>

            <tr class="border-top fw-bold">
                <td>Net Cash from Operating Activities</td>
                <td class="text-end {{ $operatingCash >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $operatingCash >= 0 ? '+' : '' }}{{ number_format($operatingCash,0,',','.') }}
                </td>
            </tr>

            <tr><td colspan="2" class="py-1"></td></tr>

            {{-- Saldo akhir --}}
            <tr class="fw-bold border-top border-bottom table-{{ $closingCash >= 0 ? 'success' : 'danger' }}">
                <td>Ending Cash Balance</td>
                <td class="text-end fs-6">
                    Rp {{ number_format($closingCash,0,',','.') }}
                </td>
            </tr>

            <tr class="text-muted small">
                <td class="ps-2">Increase / Decrease in Cash</td>
                <td class="text-end {{ $netCashChange >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $netCashChange >= 0 ? '+' : '' }}{{ number_format($netCashChange,0,',','.') }}
                </td>
            </tr>
        </table>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, form, nav, .sidebar { display: none !important; }
    #printArea { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
@endsection