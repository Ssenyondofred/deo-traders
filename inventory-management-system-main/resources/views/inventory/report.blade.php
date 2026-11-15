@extends('layouts.app')

@section('title', 'Stock Report')
@section('page_title', 'Stock Report')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>
                            {{ match($cat) {
                                'raw_material' => 'Raw Material',
                                'finished_goods' => 'Finished Goods',
                                'fuel' => 'Fuel',
                                'packaging' => 'Packaging',
                                default => $cat
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary">Show</button>
                <a href="{{ route('stock-report.index', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Print</a>
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-link">Stock</a>
            </div>
        </form>
        <p class="small text-muted mb-0 mt-2">
            Periode mutasi: {{ $dateFrom && $dateTo ? $dateFrom.' s/d '.$dateTo : 'All periods' }}
            — Gudang: {{ $warehouse->name }}
        </p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label">Number of Product Types</div>
            <div class="value">{{ $summary->product_count }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label">Total Remaining Qty (all units)</div>
            <div class="value" style="font-size:18px">{{ number_format($summary->total_qty, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label">Total Stock Value</div>
            <div class="value" style="font-size:18px">Rp {{ number_format($summary->total_value, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th class="text-end">Qty In*</th>
                    <th class="text-end">Qty Out*</th>
                    <th class="text-end">Qty On Hand</th>
                    <th class="text-end">Avg Cost</th>
                    <th class="text-end">Stock Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->code }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->category }}</td>
                        <td>{{ $row->unit }}</td>
                        <td class="text-end">{{ number_format($row->qty_in, 2) }}</td>
                        <td class="text-end">{{ number_format($row->qty_out, 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row->qty_on_hand, 2) }}</td>
                        <td class="text-end">Rp {{ number_format($row->avg_cost, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->stock_value, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="8" class="text-end">TOTAL STOCK VALUE</th>
                    <th class="text-end">Rp {{ number_format($summary->total_value, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer small text-muted">* Qty in/out follows the period filter. Qty on hand = current balance.</div>
</div>
@endsection
