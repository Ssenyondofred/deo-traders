@extends('layouts.app')
@section('title', 'Accounts Receivable')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-cash-coin me-2"></i>Accounts Receivable</h5>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body py-2">
                <div class="text-muted small">Not Paid Off</div>
                <div class="fw-bold text-danger">Rp {{ number_format($summary['total_open'],0,',','.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body py-2">
                <div class="text-muted small">Partial Payments</div>
                <div class="fw-bold text-warning">Rp {{ number_format($summary['total_partial'],0,',','.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body py-2">
                <div class="text-muted small">Already Paid (total)</div>
                <div class="fw-bold text-success">Rp {{ number_format($summary['total_paid'],0,',','.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari customer..." value="{{ request('search') }}">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="open" @selected(request('status')=='open')>Not Paid Off</option>
            <option value="partial" @selected(request('status')=='partial')>Partial Payments</option>
            <option value="paid" @selected(request('status')=='paid')>Already Paid</option>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Filter</button>
        <a href="{{ route('ar.index') }}" class="btn btn-sm btn-secondary">Reset</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Dokumen</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Due Date</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($query as $ar)
                <tr>
                    <td><code>{{ $ar->document_no }}</code></td>
                    <td>{{ $ar->created_at->format('d/m/Y') }}</td>
                    <td>{{ $ar->customer->name }}</td>
                    <td>
                        @if($ar->due_date)
                            @php $overdue = $ar->status !== 'paid' && $ar->due_date->isPast() @endphp
                            <span class="{{ $overdue ? 'text-danger fw-bold' : '' }}">
                                {{ $ar->due_date->format('d/m/Y') }}
                                {{ $overdue ? '⚠' : '' }}
                            </span>
                        @else -
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($ar->original_amount,0,',','.') }}</td>
                    <td class="text-end text-success">{{ number_format($ar->paid_amount,0,',','.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($ar->balance_amount,0,',','.') }}</td>
                    <td>{!! $ar->status_label !!}</td>
                    <td>
                        <a href="{{ route('ar.show', $ar) }}" class="btn btn-xs btn-outline-primary btn-sm">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No receivable data available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $query->links() }}</div>
@endsection