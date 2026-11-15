@extends('layouts.app')

@section('title', 'Stok Barang')
@section('page_title', 'Inventory / Stock')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Search Product</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name / Code...">
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-3 text-end">
                @if(auth()->user()->hasPermission('inventory.adjust'))
                    <a href="{{ route('inventory.adjustment.create') }}" class="btn btn-sm btn-success">+ Stock Adjustment</a>
                @endif
                @if(auth()->user()->hasPermission('stock_report.view'))
                    <a href="{{ route('stock-report.index') }}" class="btn btn-sm btn-outline-secondary">Stock Report</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between">
        <span class="fw-semibold">Stock List — {{ $warehouse->name }}</span>
        <span class="small text-muted">{{ $items->count() }} items</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th class="text-end">Quantity In</th>
                    <th class="text-end">Quantity Out</th>
                    <th class="text-end">Quantity on Hand</th>
                    <th class="text-end">Average Price</th>
                    <th class="text-end">Stock Value</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $row)
                    <tr>
                        <td>{{ $row->product->code }}</td>
                        <td>{{ $row->product->name }}</td>
                        <td><span class="badge text-bg-light border">{{ $row->product->categoryLabel() }}</span></td>
                        <td class="text-end">{{ number_format($row->qty_in, 2) }} {{ $row->product->unit }}</td>
                        <td class="text-end">{{ number_format($row->qty_out, 2) }} {{ $row->product->unit }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row->qty_on_hand, 2) }} {{ $row->product->unit }}</td>
                        <td class="text-end">Rp {{ number_format($row->avg_cost, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($row->stock_value, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('inventory.show', $row->product) }}" class="btn btn-xs btn-sm btn-outline-primary">Stock Card</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No product data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
