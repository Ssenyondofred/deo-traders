@extends('layouts.app')

@section('title', $batch ? 'Edit Produksi' : 'Produksi Baru')
@section('page_title', $batch ? 'Edit Batch Produksi' : 'Batch Produksi Baru')

@section('content')
<div class="mb-3">
    <a href="{{ route('production.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Daftar</a>
</div>

<div class="row g-3 mb-3">
    @foreach($stock as $code => $s)
        <div class="col-md-4">
            <div class="stat-card">
                <div class="label">Stok {{ $s['label'] }}</div>
                <div class="value" style="font-size:18px">{{ number_format($s['qty'], 2) }} {{ $s['unit'] }}</div>
                <div class="small text-muted">Avg: Rp {{ number_format($s['avg_cost'], 0, ',', '.') }}</div>
            </div>
        </div>
    @endforeach
</div>

<form method="POST" action="{{ $batch ? route('production.update', $batch) : route('production.store') }}">
    @csrf
    @if($batch) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Information Batch</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Production Date</label>
                            <input type="date" name="production_date" class="form-control" required
                                   value="{{ old('production_date', $batch?->production_date?->format('Y-m-d') ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Batch Label (optional)</label>
                            <input type="text" name="batch_label" class="form-control" maxlength="100"
                                   placeholder="Example: Batch May-1 / Dryer A"
                                   value="{{ old('batch_label', $batch?->batch_label) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $batch?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Input Materials & Results</div>
                <div class="card-body">
                    @php
                        $rawQty = old('raw_corn_qty', $batch?->materials->first(fn ($m) => $m->product?->code === 'RM-CORN')?->qty);
                        $coalQty = old('coal_qty', $batch?->materials->first(fn ($m) => $m->product?->code === 'RM-COAL')?->qty);
                        $outQty = old('output_qty', $batch?->outputs->first()?->qty);
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Raw Corn (Kg)</label>
                            <input type="number" name="raw_corn_qty" class="form-control" step="0.0001" min="0.0001" required value="{{ $rawQty }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Coal (Kg)</label>
                            <input type="number" name="coal_qty" class="form-control" step="0.0001" min="0.0001" required value="{{ $coalQty }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dried Corn — Output (Kg)</label>
                            <input type="number" name="output_qty" class="form-control" step="0.0001" min="0.0001" required value="{{ $outQty }}">
                        </div>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        When approved: raw material & coal stock decreases, dry stock increases.  
                        The cost of goods produced is calculated from the total material cost (average cost) ÷ quantity produced.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Save Draft</button>
                    <button type="submit" name="submit_after_save" value="1" class="btn btn-success">Save & Submit for Approval</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
