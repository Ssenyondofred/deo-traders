@extends('layouts.app')

@section('title', $purchase->purchase_no)
@section('page_title', 'Detail Pembelian — ' . $purchase->purchase_no)

@section('content')
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">&larr; List</a>
    @if($purchase->isEditable() && auth()->user()->hasPermission('purchases.create'))
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-primary">Edit</a>
    @endif
    @if(in_array($purchase->status, ['draft','rejected']) && auth()->user()->hasPermission('purchases.create'))
        <form method="POST" action="{{ route('purchases.submit', $purchase) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Submit to admin?')">Submit for Approval</button>
        </form>
    @endif
    @if($purchase->status === 'pending_approval' && auth()->user()->hasPermission('purchases.approve'))
        <form method="POST" action="{{ route('purchases.approve', $purchase) }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Approve & posting stok+jurnal?')">Approve & Post</button>
        </form>
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="small text-muted">Supplier</div>
                        <div class="fw-semibold">{{ $purchase->supplier->name }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Date</div>
                        <div>{{ $purchase->purchase_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Status</div>
                        <span class="badge text-bg-{{ $purchase->statusBadgeClass() }}">{{ $purchase->statusLabel() }}</span>
                    </div>
                    <div class="col-md-4 mt-2">
                        <div class="small text-muted">Payment</div>
                        <div>{{ $purchase->payment_method === 'cash' ? 'Cash' : 'Credit' }}</div>
                    </div>
                    @if($purchase->cashBankAccount)
                        <div class="col-md-4 mt-2">
                            <div class="small text-muted">Cash/Bank</div>
                            <div>{{ $purchase->cashBankAccount->name }}</div>
                        </div>
                    @endif
                    @if($purchase->due_date)
                        <div class="col-md-4 mt-2">
                            <div class="small text-muted">Due Date</div>
                            <div>{{ $purchase->due_date->format('d/m/Y') }}</div>
                        </div>
                    @endif
                </div>
                @if($purchase->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0 small">Reason for rejection: {{ $purchase->rejection_reason }}</div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Purchase Item</div>
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th class="text-end">Quantity</th><th class="text-end">Price</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-end">{{ number_format($item->qty, 2) }} {{ $item->product->unit }}</td>
                            <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="3" class="text-end">Subtotal</th><th class="text-end">Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</th></tr>
                    @if($purchase->discount > 0)
                        <tr><th colspan="3" class="text-end">Discount</th><th class="text-end">Rp {{ number_format($purchase->discount, 0, ',', '.') }}</th></tr>
                    @endif
                    @if($purchase->tax_amount > 0)
                        <tr><th colspan="3" class="text-end">PPN ({{ $purchase->tax_percent }}%)</th><th class="text-end">Rp {{ number_format($purchase->tax_amount, 0, ',', '.') }}</th></tr>
                    @endif
                    <tr><th colspan="3" class="text-end">Grand Total</th><th class="text-end">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</th></tr>
                </tfoot>
            </table>
        </div>

        @if($purchase->journalEntry)
            <div class="card">
                <div class="card-header bg-white fw-semibold">Automatic Journal — {{ $purchase->journalEntry->entry_no }}</div>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Akun</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->journalEntry->lines as $line)
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
        @if($purchase->apDocument)
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Accounts Payable (AP)</div>
                <div class="card-body small">
                    <div>No: {{ $purchase->apDocument->document_no }}</div>
                    <div>Status: <strong>{{ $purchase->apDocument->statusLabel() }}</strong></div>
                    <div>Balance: Rp {{ number_format($purchase->apDocument->balance_amount, 0, ',', '.') }}</div>
                    <div class="text-muted mt-2">Payment of accounts payable in the AP module (Step 7).</div>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-body small text-muted">
                <div>Created: {{ $purchase->creator?->name }}</div>
                @if($purchase->submitted_at)<div>Submitted: {{ $purchase->submitted_at->format('d/m/Y H:i') }}</div>@endif
                @if($purchase->approver)<div>Approved: {{ $purchase->approver->name }} ({{ $purchase->posted_at?->format('d/m/Y H:i') }})</div>@endif
            </div>
        </div>
    </div>
</div>

@if($purchase->status === 'pending_approval' && auth()->user()->hasPermission('purchases.approve'))
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('purchases.reject', $purchase) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Reject Purchase</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Reason for rejection</label>
                <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
