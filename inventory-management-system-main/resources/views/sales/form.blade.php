@extends('layouts.app')

@section('title', $sale ? 'Edit Sales' : 'New Sales')
@section('page_title', $sale ? 'Edit Sales' : 'New Sales')

@section('content')
<div class="mb-3">
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">&larr; List</a>
</div>

<form method="POST" action="{{ $sale ? route('sales.update', $sale) : route('sales.store') }}" id="saleForm">
    @csrf
    @if($sale) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Sales Header</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="sale_date" class="form-control" required
                                   value="{{ old('sale_date', $sale?->sale_date?->format('Y-m-d') ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">— Pilih —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id', $sale?->customer_id)==$c->id)>{{ $c->code }} — {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="cash" @selected(old('payment_method', $sale?->payment_method)==='cash')>Cash</option>
                                <option value="credit" @selected(old('payment_method', $sale?->payment_method)==='credit')>Credit</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="cashWrap">
                            <label class="form-label">Cash/Bank Receipt</label>
                            <select name="cash_bank_account_id" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach($cashAccounts as $cb)
                                    <option value="{{ $cb->id }}" @selected(old('cash_bank_account_id', $sale?->cash_bank_account_id)==$cb->id)>{{ $cb->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" id="dueWrap" style="display:none">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ old('due_date', $sale?->due_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Sales Item — {{ $product->name }}</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Quantity ({{ $product->unit }})</label>
                            <input type="number" name="qty" id="qty" class="form-control" step="0.0001" min="0.0001" required
                                   value="{{ old('qty', $mainItem?->qty ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price / {{ $product->unit }}</label>
                            <input type="number" name="unit_price" id="unit_price" class="form-control" step="1" min="0" required
                                   value="{{ old('unit_price', $mainItem?->unit_price ?? $product->currentUnitPrice()) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Automatic Sub-Inventory (BOM)</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Item</th><th class="text-end">Quantity Used</th><th>Unit</th></tr>
                        </thead>
                        <tbody id="bomPreview">
                            @foreach($bomRules as $b)
                                <tr data-per-unit="{{ $b['qty_per_unit'] }}">
                                    <td>{{ $b['name'] }}</td>
                                    <td class="text-end bom-qty">{{ number_format($b['qty'], 4) }}</td>
                                    <td>{{ $b['unit'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-2 small text-muted">Sacks, ropes, and diesel are automatically deducted upon approval.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top:1rem">
                <div class="card-header bg-white fw-semibold">Summary</div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small">Discount (Rp)</label>
                        <input type="number" name="discount" id="discount" class="form-control form-control-sm" min="0"
                               value="{{ old('discount', $sale?->discount ?? 0) }}">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_taxable" id="is_taxable" value="1"
                               @checked(old('is_taxable', $sale?->is_taxable))>
                        <label class="form-check-label" for="is_taxable">Is Taxable</label>
                    </div>
                    <div class="mb-2">
                        <input type="number" name="tax_percent" id="tax_percent" class="form-control form-control-sm" min="0" max="100"
                               value="{{ old('tax_percent', $sale?->tax_percent ?? 11) }}">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between small"><span>Subtotal</span><span id="sumSub">Rp 0</span></div>
                    <div class="d-flex justify-content-between small"><span>PPN</span><span id="sumTax">Rp 0</span></div>
                    <div class="d-flex justify-content-between fw-bold mt-2"><span>Total</span><span id="sumGrand">Rp 0</span></div>
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Save Draft</button>
                        <button type="submit" name="submit_after_save" value="1" class="btn btn-success">Save & Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const bomRules = @json(collect($bomRules)->map(fn($b) => ['qty_per_unit' => $b['qty_per_unit']])->values());

function formatRp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

function recalc() {
    const qty = parseFloat(document.getElementById('qty').value) || 0;
    const price = parseFloat(document.getElementById('unit_price').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const isTax = document.getElementById('is_taxable').checked;
    const taxPct = parseFloat(document.getElementById('tax_percent').value) || 0;

    document.querySelectorAll('#bomPreview tr').forEach((row, i) => {
        const perUnit = parseFloat(row.dataset.perUnit) || 0;
        const cell = row.querySelector('.bom-qty');
        if (cell) cell.textContent = (qty * perUnit).toFixed(4);
    });

    const sub = qty * price;
    const base = Math.max(0, sub - discount);
    const tax = isTax ? base * (taxPct / 100) : 0;
    const grand = base + tax;
    document.getElementById('sumSub').textContent = formatRp(sub);
    document.getElementById('sumTax').textContent = formatRp(tax);
    document.getElementById('sumGrand').textContent = formatRp(grand);
}

function syncPayment() {
    const m = document.getElementById('payment_method').value;
    document.getElementById('cashWrap').style.display = m === 'cash' ? 'block' : 'none';
    document.getElementById('dueWrap').style.display = m === 'credit' ? 'block' : 'none';
}

['qty','unit_price','discount','is_taxable','tax_percent'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', recalc);
    document.getElementById(id)?.addEventListener('change', recalc);
});
document.getElementById('payment_method').addEventListener('change', syncPayment);
syncPayment();
recalc();
</script>
@endpush
