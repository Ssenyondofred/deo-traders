@extends('layouts.app')
@section('title', 'Neraca Saldo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-table me-2"></i>Trial Balance</h5>
    <a href="{{ route('ledger.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-1">As Of Date</label>
        <input type="date" name="as_of" class="form-control form-control-sm" value="{{ $date }}">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Show</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th class="text-end">Total Debit</th>
                    <th class="text-end">Total Credit</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
            @foreach($accounts as $acc)
                <tr>
                    <td><code>{{ $acc->code }}</code></td>
                    <td>{{ $acc->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ $acc->type }}</span></td>
                    <td class="text-end">{{ number_format($acc->tb_debit,0,',','.') }}</td>
                    <td class="text-end">{{ number_format($acc->tb_credit,0,',','.') }}</td>
                    <td class="text-end fw-bold {{ $acc->tb_balance < 0 ? 'text-danger' : '' }}">
                        {{ number_format($acc->tb_balance,0,',','.') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end">TOTAL</td>
                    <td class="text-end {{ abs($totalDebit-$totalCredit) > 1 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($totalDebit,0,',','.') }}
                    </td>
                    <td class="text-end {{ abs($totalDebit-$totalCredit) > 1 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($totalCredit,0,',','.') }}
                    </td>
                    <td class="text-end">
                        @if(abs($totalDebit-$totalCredit) < 1)
                            <span class="badge bg-success">Balance ✓</span>
                        @else
                            <span class="badge bg-danger">Not Balance ✗</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection