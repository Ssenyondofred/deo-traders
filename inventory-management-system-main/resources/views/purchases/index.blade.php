@extends('layouts.app')

@section('title', 'Purchases')
@section('page_title', 'Purchases')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" style="width:auto">
            <option value="">All statuses</option>
            @foreach(['draft','pending_approval','posted','rejected'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
    @if(auth()->user()->hasPermission('purchases.create'))
        <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary">+ New Purchase</a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. PO</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Payment</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                    <tr>
                        <td><a href="{{ route('purchases.show', $p) }}">{{ $p->purchase_no }}</a></td>
                        <td>{{ $p->purchase_date->format('d/m/Y') }}</td>
                        <td>{{ $p->supplier->name }}</td>
                        <td>{{ $p->payment_method === 'cash' ? 'Cash' : 'Credit' }}</td>
                        <td class="text-end">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</td>
                        <td><span class="badge text-bg-{{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span></td>
                        <td class="small text-muted">{{ $p->creator?->name }}</td>
                        <td><a href="{{ route('purchases.show', $p) }}" class="btn btn-xs btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No purchases found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
        <div class="card-footer">{{ $purchases->links() }}</div>
    @endif
</div>
@endsection
