@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
<h5 class="fw-bold mb-4"><i class="bi bi-bar-chart-line me-2"></i>Financial Reports</h5>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-primary">
            <div class="card-body">
                <h6 class="fw-bold text-primary">Income Statement</h6>
                <p class="text-muted small">Revenue, Cost of Goods Sold, operating expenses, and net income for a specific period.</p>
                <a href="{{ route('financial.income-statement') }}" class="btn btn-primary btn-sm">View Report</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <h6 class="fw-bold text-success">Balance Sheet</h6>
                <p class="text-muted small">Position of assets, liabilities, and equity as of a specific date.</p>
                <a href="{{ route('financial.balance-sheet') }}" class="btn btn-success btn-sm">View Report</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-info">
            <div class="card-body">
                <h6 class="fw-bold text-info">Cash Flow Statement</h6>
                <p class="text-muted small">Movement of cash inflows and outflows from operating activities.</p>
                <a href="{{ route('financial.cash-flow') }}" class="btn btn-info btn-sm text-white">View Report</a>
            </div>
        </div>
    </div>
</div>
@endsection