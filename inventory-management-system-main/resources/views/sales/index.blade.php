@extends('layouts.app')

@section('title', 'Sales')
@section('page_title', 'Sales Orders')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" style="width:auto">
            <option value="">All status</option>
            @foreach(['draft','pending_approval','posted','rejected'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
    @if(auth()->user()->hasPermission('sales.create'))
        <a href="{{ route('sales.create') }}" class="btn btn-sm btn-primary">+ New Sales</a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sales Order No.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td><a href="{{ route('sales.show', $s) }}">{{ $s->sale_no }}</a></td>
                        <td>{{ $s->sale_date->format('d/m/Y') }}</td>
                        <td>{{ $s->customer->name }}</td>
                        <td>{{ $s->payment_method === 'cash' ? 'Cash' : 'Credit' }}</td>
                        <td class="text-end">Rp {{ number_format($s->grand_total, 0, ',', '.') }}</td>
                        <td><span class="badge text-bg-{{ $s->statusBadgeClass() }}">{{ $s->statusLabel() }}</span></td>
                        <td><a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
        <div class="card-footer">{{ $sales->links() }}</div>
    @endif
</div>
@endsection
