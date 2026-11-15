@extends('layouts.app')
@section('title', 'General Journal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>General Journal</h5>
    <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm">+ Jurnal Manual</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="search" class="form-control form-control-sm"
            placeholder="No. dokumen / deskripsi..." value="{{ request('search') }}">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <option value="draft"  @selected(request('status')=='draft')>Draft</option>
            <option value="posted" @selected(request('status')=='posted')>Posted</option>
            <option value="void"   @selected(request('status')=='void')>Void</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="type" class="form-select form-select-sm">
            <option value="">Semua Tipe</option>
            <option value="manual"   @selected(request('type')=='manual')>Manual</option>
            <option value="auto"     @selected(request('type')=='auto')>Otomatis</option>
            <option value="reversal" @selected(request('type')=='reversal')>Pembalik</option>
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
        <a href="{{ route('journals.index') }}" class="btn btn-sm btn-secondary">Reset</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Jurnal</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td><code>{{ $entry->document_no }}</code></td>
                    <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($entry->description, 50) }}</td>
                    <td>
                        @php $typeMap = ['manual'=>'secondary','auto'=>'info','reversal'=>'warning'] @endphp
                        <span class="badge bg-{{ $typeMap[$entry->entry_type] ?? 'light' }}">
                            {{ ucfirst($entry->entry_type) }}
                        </span>
                    </td>
                    <td>{!! $entry->status_badge !!}</td>
                    <td>{{ $entry->createdBy->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('journals.show', $entry) }}"
                           class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada jurnal.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $entries->links() }}</div>
@endsection