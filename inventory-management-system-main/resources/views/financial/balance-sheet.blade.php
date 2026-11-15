@extends('layouts.app')
@section('title', 'Neraca')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Balance Sheet</h5>
    <div>
        <a href="{{ route('financial.index') }}" class="btn btn-sm btn-secondary me-1">← Back</a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-dark">🖨 Print</button>
    </div>
</div>

<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-1">As of Date</label>
        <input type="date" name="as_of" class="form-control form-control-sm" value="{{ $asOf }}">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Show</button>
    </div>
</form>

<div class="card shadow-sm" id="printArea">
    <div class="card-body">
        <div class="text-center mb-3">
            <h6 class="fw-bold mb-0">Balance Sheet</h6>
            <div class="text-muted small">As of {{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }}</div>
        </div>

        <div class="row">
            {{-- ASET --}}
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr class="fw-bold table-primary">
                        <td colspan="2">Aset</td>
                    </tr>

                    <tr class="fw-bold table-light">
                        <td colspan="2">Current Assets</td>
                    </tr>
                    @foreach($currentAssets as $acc)
                    <tr>
                        <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                        <td class="text-end">{{ number_format($acc->balance,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top fw-bold">
                        <td class="ps-4">Total Current Assets</td>
                        <td class="text-end">{{ number_format($totalCurrentAssets,0,',','.') }}</td>
                    </tr>

                    <tr><td colspan="2" class="py-1"></td></tr>

                    <tr class="fw-bold table-light">
                        <td colspan="2">Fixed Assets</td>
                    </tr>
                    @foreach($fixedAssets as $acc)
                    <tr>
                        <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                        <td class="text-end">{{ number_format($acc->balance,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top fw-bold">
                        <td class="ps-4">Total Fixed Assets</td>
                        <td class="text-end">{{ number_format($totalFixedAssets,0,',','.') }}</td>
                    </tr>

                    <tr class="fw-bold border-top border-bottom table-primary">
                        <td>TOTAL ASSETS</td>
                        <td class="text-end">{{ number_format($totalAssets,0,',','.') }}</td>
                    </tr>
                </table>
            </div>

            {{-- KEWAJIBAN & EKUITAS --}}
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr class="fw-bold table-warning">
                        <td colspan="2">LIABILITIES & EQUITY</td>
                    </tr>

                    <tr class="fw-bold table-light">
                        <td colspan="2">Current Liabilities</td>
                    </tr>
                    @foreach($currentLiab as $acc)
                    <tr>
                        <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                        <td class="text-end">{{ number_format($acc->balance,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top fw-bold">
                        <td class="ps-4">Total Current Liabilities</td>
                        <td class="text-end">{{ number_format($totalCurrentLiab,0,',','.') }}</td>
                    </tr>

                    @if($longTermLiab->count() > 0)
                    <tr><td colspan="2" class="py-1"></td></tr>
                    <tr class="fw-bold table-light">
                        <td colspan="2">Long-term Liabilities</td>
                    </tr>
                    @foreach($longTermLiab as $acc)
                    <tr>
                        <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                        <td class="text-end">{{ number_format($acc->balance,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top fw-bold">
                        <td class="ps-4">Total Long-term Liabilities</td>
                        <td class="text-end">{{ number_format($totalLongTermLiab,0,',','.') }}</td>
                    </tr>
                    @endif

                    <tr class="border-top fw-bold">
                        <td>Total Liabilities</td>
                        <td class="text-end">{{ number_format($totalLiabilities,0,',','.') }}</td>
                    </tr>

                    <tr><td colspan="2" class="py-1"></td></tr>

                    <tr class="fw-bold table-light">
                        <td colspan="2">Equity</td>
                    </tr>
                    @foreach($equity as $acc)
                    <tr>
                        <td class="ps-4">{{ $acc->code }} — {{ $acc->name }}</td>
                        <td class="text-end">{{ number_format($acc->balance,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="ps-4">Retained Earnings</td>
                        <td class="text-end">{{ number_format($retainedEarnings,0,',','.') }}</td>
                    </tr>
                    <tr class="border-top fw-bold">
                        <td class="ps-4">Total Equity</td>
                        <td class="text-end">{{ number_format($totalEquity,0,',','.') }}</td>
                    </tr>

                    <tr class="fw-bold border-top border-bottom table-warning">
                        <td>TOTAL LIABILITIES & EQUITY</td>
                        <td class="text-end">{{ number_format($totalLiabEquity,0,',','.') }}</td>
                    </tr>

                    {{-- Cek balance --}}
                    @if(abs($totalAssets - $totalLiabEquity) < 1)
                    <tr><td colspan="2" class="text-end">
                        <span class="badge bg-success">Balance ✓</span>
                    </td></tr>
                    @else
                    <tr><td colspan="2" class="text-end">
                        <span class="badge bg-danger">Not Balance ✗ (selisih: {{ number_format(abs($totalAssets-$totalLiabEquity),0,',','.') }})</span>
                    </td></tr>
                    @endif
                </table>
            </div>
        </div>
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