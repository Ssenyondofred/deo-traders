@extends('layouts.app')

@section('title', 'Adjustment / Beginning Stock Balance')
@section('page_title', 'Adjustment / Beginning Stock Balance')

@section('content')
<div class="mb-3">
    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
</div>

<div class="card" style="max-width: 640px">
    <div class="card-header bg-white fw-semibold">Stock Adjustment Form (Admin)</div>
    <div class="card-body">
        <form method="POST" action="{{ route('inventory.adjustment.store') }}" id="adjustmentForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                    <option value="">— Select —</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id', request('product_id')) == $p->id)>
                            {{ $p->code }} — {{ $p->name }} ({{ $p->unit }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="adjustment_type" id="adjustment_type" class="form-select" required>
                    <option value="opening" @selected(old('adjustment_type') === 'opening')>Opening Balance</option>
                    <option value="adjustment_in" @selected(old('adjustment_type', 'adjustment_in') === 'adjustment_in')>Adjustment In</option>
                    <option value="adjustment_out" @selected(old('adjustment_type') === 'adjustment_out')>Adjustment Out</option>
                </select>
            </div>

            <input type="hidden" name="direction" id="direction" value="{{ old('direction', 'in') }}">

            <div class="mb-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="movement_date" class="form-control" value="{{ old('movement_date', now()->toDateString()) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="qty" step="0.0001" min="0.0001" class="form-control" value="{{ old('qty') }}" required>
            </div>

            <div class="mb-3" id="unitCostGroup">
                <label class="form-label">Unit Cost (for stock in)</label>
                <input type="number" name="unit_cost" step="0.01" min="0" class="form-control" value="{{ old('unit_cost', 0) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Batch No. (optional)</label>
                <input type="text" name="batch_no" class="form-control" value="{{ old('batch_no') }}" maxlength="50">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes <span class="text-danger">*</span></label>
                <textarea name="notes" class="form-control" rows="3" required placeholder="Example: Opening inventory, stock count correction, etc.">{{ old('notes') }}</textarea>
                @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const typeSelect = document.getElementById('adjustment_type');
    const directionInput = document.getElementById('direction');
    const unitCostGroup = document.getElementById('unitCostGroup');

    function syncForm() {
        const type = typeSelect.value;
        if (type === 'adjustment_out') {
            directionInput.value = 'out';
            unitCostGroup.style.display = 'none';
        } else {
            directionInput.value = 'in';
            unitCostGroup.style.display = 'block';
        }
    }

    typeSelect.addEventListener('change', syncForm);
    syncForm();
</script>
@endpush
