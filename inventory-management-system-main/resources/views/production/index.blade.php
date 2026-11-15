@extends('layouts.app')

@section('title', 'Production')
@section('page_title', 'Production / Drying')

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
    @if(auth()->user()->hasPermission('production.create'))
        <a href="{{ route('production.create') }}" class="btn btn-sm btn-primary">+ Batch Production</a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch No.</th>
                    <th>Date</th>
                    <th>Label</th>
                    <th class="text-end">Output (Kg)</th>
                    <th class="text-end">Material Cost</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                    <tr>
                        <td><a href="{{ route('production.show', $b) }}">{{ $b->batch_no }}</a></td>
                        <td>{{ $b->production_date->format('d/m/Y') }}</td>
                        <td>{{ $b->batch_label ?? '-' }}</td>
                        <td class="text-end">{{ $b->status === 'posted' ? number_format($b->total_output_qty, 2) : '-' }}</td>
                        <td class="text-end">{{ $b->status === 'posted' ? 'Rp '.number_format($b->total_material_cost, 0, ',', '.') : '-' }}</td>
                        <td><span class="badge text-bg-{{ $b->statusBadgeClass() }}">{{ $b->statusLabel() }}</span></td>
                        <td><a href="{{ route('production.show', $b) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No production batches available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches->hasPages())
        <div class="card-footer">{{ $batches->links() }}</div>
    @endif
</div>
@endsection
