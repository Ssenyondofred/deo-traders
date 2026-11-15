@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h5 class="fw-bold mb-4">Dashboard — {{ auth()->user()->name }}</h5>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sales (This Month)</div>
                <div class="fw-bold fs-5 text-success">{{ $mySales }}</div>
                <div class="text-muted" style="font-size:11px">transactions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Purchases (This Month)</div>
                <div class="fw-bold fs-5 text-primary">{{ $myPurchases }}</div>
                <div class="text-muted" style="font-size:11px">transactions</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 {{ $pendingSales > 0 ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="text-muted small mb-1">Pending Sales</div>
                <div class="fw-bold {{ $pendingSales > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ $pendingSales }} pending
                </div>
                @if($pendingSales > 0)
                <div style="font-size:11px" class="text-muted">Waiting for admin approval</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 {{ $pendingPurchases > 0 ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="text-muted small mb-1">Pending Purchases</div>
                <div class="fw-bold {{ $pendingPurchases > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ $pendingPurchases }} pending
                </div>
                @if($pendingPurchases > 0)
                <div style="font-size:11px" class="text-muted">Waiting for admin approval</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-bold small">Current Stock Condition</div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th class="text-end">Remaining Qty</th>
                    <th>Unit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($stockItems as $stock)
                <tr>
                    <td><code>{{ $stock->product->code ?? '-' }}</code></td>
                    <td>{{ $stock->product->name ?? '-' }}</td>
                    <td>{{ $stock->product->category ?? '-' }}</td>
                    <td class="text-end fw-bold {{ $stock->qty_on_hand < 10 ? 'text-danger' : '' }}">
                        {{ number_format($stock->qty_on_hand, 2) }}
                    </td>
                    <td>{{ $stock->product->unit ?? '-' }}</td>
                    <td>
                        @if($stock->qty_on_hand <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($stock->qty_on_hand < 10)
                            <span class="badge bg-warning text-dark">Low Stock</span>
                        @else
                            <span class="badge bg-success">In Stock</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">There is no stock data yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection