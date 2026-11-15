@extends('layouts.app')
@section('title', 'General Ledger')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-book me-2"></i>General Ledger</h5>
    <a href="{{ route('ledger.trial-balance') }}" class="btn btn-sm btn-outline-primary">Trial Balance</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Normal Balance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($accounts as $acc)
                <tr>
                    <td><code>{{ $acc->code }}</code></td>
                    <td>{{ $acc->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ $acc->type }}</span></td>
                    <td>{{ ucfirst($acc->normal_balance) }}</td>
                    <td>
                        <a href="{{ route('ledger.show', $acc) }}" class="btn btn-sm btn-outline-primary">
                            View General Ledger
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection