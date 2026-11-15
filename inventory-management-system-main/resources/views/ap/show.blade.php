@extends('layouts.app')
@section('title', 'Detail Account Payable')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Detail Account Payable: <code>{{ $ap->document_no }}</code></h5>
    <a href="{{ route('ap.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<div class="row">
    {{-- Info Dokumen --}}
    <div class="col-md-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-bold">Payable Information</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="40%">Supplier</th><td>{{ $ap->supplier->name }}</td></tr>
                    <tr><th>Date</th><td>{{ $ap->document_date?->format('d/m/Y') ?? '-' }}</td></tr>
                    <tr><th>Due Date</th><td>{{ $ap->due_date?->format('d/m/Y') ?? '-' }}</td></tr>
                    <tr><th>Total Payable</th><td>Rp {{ number_format($ap->original_amount,0,',','.') }}</td></tr>
                    <tr><th>Paid</th><td class="text-success">Rp {{ number_format($ap->paid_amount,0,',','.') }}</td></tr>
                    <tr><th>Balance</th><td class="fw-bold text-danger">Rp {{ number_format($ap->balance_amount,0,',','.') }}</td></tr>
                    <tr><th>Status</th><td>{!! $ap->status_label !!}</td></tr>
                </table>
            </div>
        </div>

        {{-- Form Bayar --}}
        @if($ap->status !== 'paid')
        <div class="card shadow-sm border-primary">
            <div class="card-header fw-bold text-primary">Payment Input</div>
            <div class="card-body">
                <form action="{{ route('ap.pay', $ap) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Amount (Rp) — Balance: {{ number_format($ap->balance_amount,0,',','.') }}</label>
                        <input type="number" name="amount" class="form-control form-control-sm"
                            max="{{ $ap->balance_amount }}" min="1" required
                            placeholder="Enter received amount">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Cash / Bank Received</label>
                        <select name="cash_bank_account_id" class="form-select form-select-sm" required>
                            <option value="">-- Select --</option>
                            @foreach($cashBanks as $cb)
                                <option value="{{ $cb->id }}">{{ $cb->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Notes</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100"
                        onclick="return confirm('Confirm this payment?')">
                        Save Payment
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Histori Pembayaran --}}
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Payment History</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Receipt</th>
                            <th>Date</th>
                            <th>Cash/Bank</th>
                            <th class="text-end">Amount</th>
                            <th>Journal</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($ap->payments as $pay)
                        <tr>
                            <td><code>{{ $pay->document_no }}</code></td>
                            <td>{{ $pay->payment_date->format('d/m/Y') }}</td>
                            <td>{{ $pay->cashBankAccount->name ?? '-' }}</td>
                            <td class="text-end text-success fw-bold">{{ number_format($pay->amount,0,',','.') }}</td>
                            <td>
                                @if($pay->journalEntry)
                                    <a href="{{ route('journals.show', $pay->journalEntry) }}" class="btn btn-xs btn-outline-secondary btn-sm">JE</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No payment yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection