@extends('layouts.app')

@section('title', $sale->sale_no)
@section('page_title', 'Detail Penjualan — ' . $sale->sale_no)

@section('content')
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">&larr; List Sales</a>
    @if($sale->isEditable() && auth()->user()->hasPermission('sales.create'))
        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-primary">Edit</a>
    @endif
    @if(in_array($sale->status, ['draft','rejected']) && auth()->user()->hasPermission('sales.create'))
        <form method="POST" action="{{ route('sales.submit', $sale) }}" class="d-inline">@csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Submit?')">Submit Approval</button>
        </form>
    @endif
    @if($sale->status === 'pending_approval' && auth()->user()->hasPermission('sales.approve'))
        <form method="POST" action="{{ route('sales.approve', $sale) }}" class="d-inline">@csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Approve & posting?')">Approve & Post</button>
        </form>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="small text-muted">Customer</div><div class="fw-semibold">{{ $sale->customer->name }}</div></div>
                    <div class="col-md-3"><div class="small text-muted">Date</div>{{ $sale->sale_date->format('d/m/Y') }}</div>
                    <div class="col-md-3"><span class="badge text-bg-{{ $sale->statusBadgeClass() }}">{{ $sale->statusLabel() }}</span></div>
                </div>
                @if($sale->rejection_reason)<div class="alert alert-danger mt-2 mb-0 small">{{ $sale->rejection_reason }}</div>@endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Item & BOM Details</div>
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Item</th><th>Type</th><th class="text-end">Quantity</th><th class="text-end">Price</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>
                                @if($item->is_main_product)<span class="badge text-bg-primary">Sell</span>
                                @else<span class="badge text-bg-light border">BOM</span>@endif
                            </td>
                            <td class="text-end">{{ number_format($item->qty, 2) }} {{ $item->product->unit }}</td>
                            <td class="text-end">{{ $item->is_main_product ? 'Rp '.number_format($item->unit_price,0,',','.') : '-' }}</td>
                            <td class="text-end">{{ $item->is_main_product ? 'Rp '.number_format($item->line_total,0,',','.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-end">Grand Total</th><th class="text-end">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</th></tr>
                    @if($sale->status === 'posted')
                        <tr><th colspan="4" class="text-end">HPP</th><th class="text-end">Rp {{ number_format($sale->total_cogs, 0, ',', '.') }}</th></tr>
                        <tr><th colspan="4" class="text-end">Gross Profit</th><th class="text-end">Rp {{ number_format($sale->grand_total - $sale->total_cogs, 0, ',', '.') }}</th></tr>
                    @endif
                </tfoot>
            </table>
        </div>

        @if($sale->journalEntry)
            <div class="card">
                <div class="card-header bg-white fw-semibold">Jurnal — {{ $sale->journalEntry->entry_no }}</div>
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
                    <tbody>
                        @foreach($sale->journalEntry->lines as $line)
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
        @if($sale->arDocument)
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Accounts Receivable (AR)</div>
                <div class="card-body small">
                    <div>Status: <strong>{{ $sale->arDocument->statusLabel() }}</strong></div>
                    <div>Balance: Rp {{ number_format($sale->arDocument->balance_amount, 0, ',', '.') }}</div>
                    <div class="text-muted mt-1">Settlement in AR module (Step 7).</div>
                </div>
            </div>
        @endif
        @if($sale->cashBankAccount && $sale->status === 'posted' && $sale->payment_method === 'cash')
            <div class="card mb-3">
                <div class="card-body small">Cash received in: <strong>{{ $sale->cashBankAccount->name }}</strong></div>
            </div>
        @endif
    </div>
</div>

@if($sale->status === 'pending_approval' && auth()->user()->hasPermission('sales.approve'))
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('sales.reject', $sale) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Reject Sales</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><textarea name="rejection_reason" class="form-control" rows="3" required></textarea></div>
            <div class="modal-footer"><button type="submit" class="btn btn-danger">Reject</button></div>
        </form>
    </div>
</div>
@endif
@endsection
