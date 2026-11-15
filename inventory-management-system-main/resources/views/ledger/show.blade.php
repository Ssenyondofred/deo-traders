@extends('layouts.app')
@section('title', 'Ledger: '.$account->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Ledger: {{ $account->code }} — {{ $account->name }}</h5>
    <a href="{{ route('ledger.index') }}" class="btn btn-sm btn-secondary">← Back</a>
</div>

<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-1">Account</label>
        <select name="account_id" class="form-select form-select-sm"
            onchange="window.location='/ledger/'+this.value+'?from={{ $from }}&to={{ $to }}'">
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" @selected($acc->id === $account->id)>
                    {{ $acc->code }} — {{ $acc->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Show</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Journal No.</th>
                    <th>Description</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                {{-- Saldo awal --}}
                <tr class="table-secondary fw-bold">
                    <td colspan="5">Opening Balance (before {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }})</td>
                    <td class="text-end">{{ number_format($openingBalance, 0, ',', '.') }}</td>
                </tr>

                @forelse($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $journal = \App\Models\JournalEntry::where('entry_no', $row['document_no'])->first();
                        @endphp

                        @if($journal)
                            <a href="{{ route('journals.show', $journal->id) }}">
                                <code>{{ $row['document_no'] }}</code>
                            </a>
                        @else
                            <code class="text-muted">{{ $row['document_no'] }}</code>
                        @endif
                    </td>
                    <td>{{ $row['description'] }}</td>
                    <td class="text-end">{{ $row['debit'] > 0 ? number_format($row['debit'],0,',','.') : '-' }}</td>
                    <td class="text-end">{{ $row['credit'] > 0 ? number_format($row['credit'],0,',','.') : '-' }}</td>
                    <td class="text-end fw-bold {{ $row['balance'] < 0 ? 'text-danger' : '' }}">
                        {{ number_format($row['balance'],0,',','.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No transactions found in this period.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">Closing Balance</td>
                    <td class="text-end {{ $runningBalance < 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($runningBalance,0,',','.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection