@extends('layouts.app')

@section('title', $batch->batch_no)
@section('page_title', 'Detail Produksi — ' . $batch->batch_no)

@section('content')
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('production.index') }}" class="btn btn-sm btn-outline-secondary">&larr; List</a>
    @if($batch->isEditable() && auth()->user()->hasPermission('production.create'))
        <a href="{{ route('production.edit', $batch) }}" class="btn btn-sm btn-primary">Edit</a>
    @endif
    @if(in_array($batch->status, ['draft','rejected']) && auth()->user()->hasPermission('production.create'))
        <form method="POST" action="{{ route('production.submit', $batch) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Submit to admin?')">Submit for Approval</button>
        </form>
    @endif
    @if($batch->status === 'pending_approval' && auth()->user()->hasPermission('production.approve'))
        <form method="POST" action="{{ route('production.approve', $batch) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Approve & posting stok?')">Approve & Post</button>
        </form>
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="small text-muted">Date</div>
                        <div>{{ $batch->production_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Batch Label</div>
                        <div>{{ $batch->batch_label ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Status</div>
                        <span class="badge text-bg-{{ $batch->statusBadgeClass() }}">{{ $batch->statusLabel() }}</span>
                    </div>
                </div>
                @if($batch->notes)
                    <div class="mt-2 small"><strong>Notes:</strong> {{ $batch->notes }}</div>
                @endif
                @if($batch->rejection_reason)
                    <div class="alert alert-danger mt-2 mb-0 small">{{ $batch->rejection_reason }}</div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Raw Materials (Out)</div>
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Item</th><th class="text-end">Quantity</th><th class="text-end">Price/Unit</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($batch->materials as $m)
                        <tr>
                            <td>{{ $m->product->name }}</td>
                            <td class="text-end">{{ number_format($m->qty, 2) }} {{ $m->product->unit }}</td>
                            <td class="text-end">{{ $batch->status === 'posted' ? 'Rp '.number_format($m->unit_cost, 0, ',', '.') : '(saat post)' }}</td>
                            <td class="text-end">{{ $batch->status === 'posted' ? 'Rp '.number_format($m->line_cost, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Production Output (In)</div>
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Item</th><th class="text-end">Quantity</th><th class="text-end">Cost/Unit</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($batch->outputs as $o)
                        <tr>
                            <td>{{ $o->product->name }}</td>
                            <td class="text-end">{{ number_format($o->qty, 2) }} {{ $o->product->unit }}</td>
                            <td class="text-end">{{ $batch->status === 'posted' ? 'Rp '.number_format($o->unit_cost, 0, ',', '.') : '(saat post)' }}</td>
                            <td class="text-end">{{ $batch->status === 'posted' ? 'Rp '.number_format($o->line_cost, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($batch->journalEntry)
            <div class="card">
                <div class="card-header bg-white fw-semibold">Jurnal — {{ $batch->journalEntry->entry_no }}</div>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr>
                    </thead>
                    <tbody>
                        @foreach($batch->journalEntry->lines as $line)
                            <tr>
                                <td>{{ $line->account->code }} — {{ $line->account->name }}</td>
                                <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        @if($batch->status === 'posted')
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Cost Summary</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between"><span>Total Raw Material Cost</span><span>Rp {{ number_format($batch->total_material_cost, 0, ',', '.') }}</span></div>
                    <div class="d-flex justify-content-between"><span>Output Quantity</span><span>{{ number_format($batch->total_output_qty, 2) }} Kg</span></div>
                    <div class="d-flex justify-content-between fw-semibold mt-2"><span>Cost per Kg</span><span>Rp {{ number_format($batch->output_unit_cost, 0, ',', '.') }}</span></div>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-body small text-muted">
                <div>Created: {{ $batch->creator?->name }}</div>
                @if($batch->submitted_at)<div>Diajukan: {{ $batch->submitted_at->format('d/m/Y H:i') }}</div>@endif
                @if($batch->approver)<div>Disetujui: {{ $batch->approver->name }}</div>@endif
            </div>
        </div>
    </div>
</div>

@if($batch->status === 'pending_approval' && auth()->user()->hasPermission('production.approve'))
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('production.reject', $batch) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Reject Batch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Reason for rejection"></textarea>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div>
</div>
@endif
@endsection
