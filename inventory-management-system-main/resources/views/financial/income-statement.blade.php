@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Income Statement</h5>
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
            <h6 class="fw-bold mb-0">INCOME STATEMENT</h6>
            <div class="text-muted small">
                Period {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} to
                {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            </div>
        </div>

        <table class="table table-sm table-borderless">

            {{-- Pendapatan --}}
            <tr class="fw-bold table-light">
                <td colspan="2">REVENUE</td>
            </tr>
            @foreach($revenue as $acc)
            <tr>
                <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                <td class="text-end">{{ number_format($acc->period_balance,0,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="border-top fw-bold">
                <td class="ps-4">Total Revenue</td>
                <td class="text-end">{{ number_format($totalRevenue,0,',','.') }}</td>
            </tr>

            <tr><td colspan="2" class="py-1"></td></tr>

            {{-- HPP --}}
            <tr class="fw-bold table-light">
                <td colspan="2">COST OF GOODS SOLD</td>
            </tr>
            @foreach($cogs as $acc)
            <tr>
                <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                <td class="text-end">({{ number_format($acc->period_balance,0,',','.') }})</td>
            </tr>
            @endforeach
            <tr class="border-top fw-bold">
                <td class="ps-4">Total Cost of Goods Sold</td>
                <td class="text-end">({{ number_format($totalCogs,0,',','.') }})</td>
            </tr>

            {{-- Laba Kotor --}}
            <tr class="fw-bold border-top border-bottom">
                <td>GROSS PROFIT</td>
                <td class="text-end {{ $grossProfit < 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($grossProfit,0,',','.') }}
                </td>
            </tr>

            <tr><td colspan="2" class="py-1"></td></tr>

            {{-- Beban Operasional --}}
            <tr class="fw-bold table-light">
                <td colspan="2">OPERATING EXPENSES</td>
            </tr>
            @foreach($expenses as $acc)
            <tr>
                <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                <td class="text-end">({{ number_format($acc->period_balance,0,',','.') }})</td>
            </tr>
            @endforeach
            <tr class="border-top fw-bold">
                <td class="ps-4">Total Operating Expenses</td>
                <td class="text-end">({{ number_format($totalExpenses,0,',','.') }})</td>
            </tr>

            {{-- Laba Operasional --}}
            <tr class="fw-bold border-top border-bottom">
                <td>OPERATING INCOME</td>
                <td class="text-end {{ $operatingProfit < 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($operatingProfit,0,',','.') }}
                </td>
            </tr>

            @if($otherIncome->count() > 0 || $otherExp->count() > 0)
            <tr><td colspan="2" class="py-1"></td></tr>
            <tr class="fw-bold table-light">
                <td colspan="2">OTHER INCOME / EXPENSES</td>
            </tr>
            @foreach($otherIncome as $acc)
            <tr>
                <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                <td class="text-end">{{ number_format($acc->period_balance,0,',','.') }}</td>
            </tr>
            @endforeach
            @foreach($otherExp as $acc)
            <tr>
                <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                <td class="text-end">({{ number_format($acc->period_balance,0,',','.') }})</td>
            </tr>
            @endforeach
            @endif

            {{-- Laba Bersih --}}
            <tr class="fw-bold border-top border-bottom table-{{ $netProfit >= 0 ? 'success' : 'danger' }}">
                <td>NET INCOME</td>
                <td class="text-end fs-6">
                    Rp {{ number_format($netProfit,0,',','.') }}
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