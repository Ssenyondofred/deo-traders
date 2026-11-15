@extends('layouts.app')
@section('title', 'Activity Log')

@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Activity Log</h5>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-2">
        <select name="user_id" class="form-select form-select-sm">
            <option value="">All Users</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="module" class="form-select form-select-sm">
            <option value="">All Modules</option>
            @foreach($modules as $mod)
                <option value="{{ $mod }}" @selected(request('module')==$mod)>{{ $mod }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
    </div>
    <div class="col-md-2">
        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary">Filter</button>
        <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-secondary">Reset</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="text-muted" style="font-size:12px">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td>{{ $log->user->name ?? '-' }}</td>
                    <td><span class="badge bg-light text-dark">{{ $log->module }}</span></td>
                    <td>
                        @php
                            $actionColor = match($log->action) {
                                'created'  => 'success',
                                'updated'  => 'warning',
                                'deleted'  => 'danger',
                                'posted'   => 'primary',
                                'void'     => 'danger',
                                'payment'  => 'info',
                                'login'    => 'secondary',
                                default    => 'light',
                            };
                        @endphp
                        <span class="badge bg-{{ $actionColor }}">{{ $log->action }}</span>
                    </td>
                    <td style="font-size:12px">{{ $log->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No log available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection