@extends('layouts.app')
@section('title', 'Detail Jurnal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Jurnal: <code>{{ $journal->document_no }}</code></h5>
    <a href="{{ route('journals.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-bold">Journal Information</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="40%">Journal No.</th><td><code>{{ $journal->document_no }}</code></td></tr>
                    <tr><th>Date</th><td>{{ $journal->entry_date->format('d/m/Y') }}</td></tr>
                    <tr><th>Description</th><td>{{ $journal->description }}</td></tr>
                    <tr><th>Type</th><td>{{ ucfirst($journal->entry_type) }}</td></tr>
                    <tr><th>Status</th><td>{!! $journal->status_badge !!}</td></tr>
                    <tr><th>Created</th><td>{{ $journal->createdBy->name ?? '-' }}</td></tr>
                    @if($journal->postedBy)
                    <tr><th>Posted</th><td>{{ $journal->postedBy->name }} — {{ $journal->posted_at->format('d/m/Y H:i') }}</td></tr>
                    @endif
                    @if($journal->notes)
                    <tr><th>Notes</th><td>{{ $journal->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Aksi --}}
        @if($journal->status === 'draft')
        <form action="{{ route('journals.post', $journal) }}" method="POST" class="mb-2">
            @csrf
            <button class="btn btn-success btn-sm w-100"
                onclick="return confirm('Post journal ini?')">
                ✓ Post Jurnal
            </button>
        </form>
        @endif

        @if($journal->status === 'posted' && $journal->entry_type === 'manual')
        <button class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#voidModal">
            Void Jurnal
        </button>
        @endif
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Row Journal</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($journal->lines as $line)
                        <tr>
                            <td><code>{{ $line->account->code }}</code></td>
                            <td>{{ $line->account->name }}</td>
                            <td class="text-muted small">{{ $line->description }}</td>
                            <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit,0,',','.') : '-' }}</td>
                            <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit,0,',','.') : '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end">{{ number_format($journal->total_debit,0,',','.') }}</td>
                            <td class="text-end">{{ number_format($journal->total_credit,0,',','.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Void --}}
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('journals.void', $journal) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Void Jurnal</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Void will create a reversing journal automatically. This action cannot be undone.</p>
                    <label class="form-label small">Reason for voiding <span class="text-danger">*</span></label>
                    <input type="text" name="reason" class="form-control form-control-sm" required placeholder="Example: Correction of input error">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Void Jurnal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection