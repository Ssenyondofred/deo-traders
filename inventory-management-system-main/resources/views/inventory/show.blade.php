@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('page_title', 'Kartu Stok — ' . $product->name)

@section('content')
<div class="mb-3">
    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
    @if(auth()->user()->hasPermission('inventory.adjust'))
        <a href="{{ route('inventory.adjustment.create', ['product_id' => $product->id]) }}" class="btn btn-sm btn-success">Penyesuaian</a>
    @endif
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Remaining Qty</div>
            <div class="value">{{ number_format($balance->qty_on_hand, 2) }} <small class="fs-6">{{ $product->unit }}</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Total In</div>
            <div class="value">{{ number_format($totals['qty_in'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Total Out</div>
            <div class="value">{{ number_format($totals['qty_out'], 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Average Cost</div>
            <div class="value" style="font-size:16px">Rp {{ number_format($balance->avg_cost, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Type</label>
                <select name="movement_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Enums\StockMovementType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('movement_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white fw-semibold">Riwayat Pergerakan Stok</div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Batch</th>
                    <th class="text-end">In</th>
                    <th class="text-end">Out</th>
                    <th class="text-end">Balance</th>
                    <th class="text-end">Price/Unit</th>
                    <th>Notes</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td>{{ $m->movement_date->format('d/m/Y') }}</td>
                        <td>{{ $m->typeLabel() }}</td>
                        <td>{{ $m->batch_no ?? '-' }}</td>
                        <td class="text-end text-success">
                            @if($m->direction === 'in'){{ number_format($m->qty, 2) }}@else-@endif
                        </td>
                        <td class="text-end text-danger">
                            @if($m->direction === 'out'){{ number_format($m->qty, 2) }}@else-@endif
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($m->qty_after, 2) }}</td>
                        <td class="text-end">Rp {{ number_format($m->unit_cost, 0, ',', '.') }}</td>
                        <td class="small">{{ $m->notes ?? '-' }}</td>
                        <td class="small text-muted">{{ $m->creator?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No stock movement yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
        <div class="card-footer">{{ $movements->links() }}</div>
    @endif
</div>
@endsection
