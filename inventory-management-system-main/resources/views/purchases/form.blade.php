@extends('layouts.app')

@section('title', $purchase ? 'Edit' : 'New Purchase')
@section('page_title', $purchase ? 'Edit Purchase' : 'New Purchase')

@section('content')
<div class="mb-3">
    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">&larr; List</a>
</div>

<form method="POST" action="{{ $purchase ? route('purchases.update', $purchase) : route('purchases.store') }}" id="purchaseForm">
    @csrf
    @if($purchase) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Purchase Header</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="purchase_date" class="form-control" required
                                   value="{{ old('purchase_date', $purchase?->purchase_date?->format('Y-m-d') ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">— Select —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" @selected(old('supplier_id', $purchase?->supplier_id)==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="cash" @selected(old('payment_method', $purchase?->payment_method)==='cash')>Cash</option>
                                <option value="credit" @selected(old('payment_method', $purchase?->payment_method)==='credit')>Credit</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="cashAccountWrap">
                            <label class="form-label">Cash/Bank Account</label>
                            <select name="cash_bank_account_id" id="cash_bank_account_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($cashAccounts as $c)
                                    <option value="{{ $c->id }}" @selected(old('cash_bank_account_id', $purchase?->cash_bank_account_id)==$c->id)>{{ $c->name }} (Saldo: Rp {{ number_format($c->balance,0,',','.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" id="dueDateWrap" style="display:none">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ old('due_date', $purchase?->due_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchase?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Item Details</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLine">+ Row</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Item</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php
                                $oldItems = old('items', $purchase ? $purchase->items->map(fn($i) => [
                                    'product_id' => $i->product_id,
                                    'qty' => $i->qty,
                                    'unit_price' => $i->unit_price,
                                ])->toArray() : [['product_id'=>'','qty'=>'','unit_price'=>'']]);
                            @endphp
                            @foreach($oldItems as $idx => $item)
                                @include('purchases._item_row', ['idx' => $idx, 'item' => $item, 'products' => $products])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top:1rem">
                <div class="card-header bg-white fw-semibold">Summary</div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small">Discount (Rp)</label>
                        <input type="number" name="discount" id="discount" class="form-control form-control-sm" min="0" step="1"
                               value="{{ old('discount', $purchase?->discount ?? 0) }}">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_taxable" id="is_taxable" value="1"
                               @checked(old('is_taxable', $purchase?->is_taxable))>
                        <label class="form-check-label" for="is_taxable">Get PPN</label>
                    </div>
                    <div class="mb-3" id="taxPercentWrap">
                        <label class="form-label small">PPN (%)</label>
                        <input type="number" name="tax_percent" id="tax_percent" class="form-control form-control-sm" min="0" max="100" step="0.01"
                               value="{{ old('tax_percent', $purchase?->tax_percent ?? 11) }}">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between small"><span>Subtotal</span><span id="sumSubtotal">Rp 0</span></div>
                    <div class="d-flex justify-content-between small"><span>PPN</span><span id="sumTax">Rp 0</span></div>
                    <div class="d-flex justify-content-between fw-bold mt-2"><span>Grand Total</span><span id="sumGrand">Rp 0</span></div>

                    <div class="mt-3 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Save Draft</button>
                        <button type="submit" name="submit_after_save" value="1" class="btn btn-success">Save & Submit for Approval</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="lineTemplate">
    @include('purchases._item_row', ['idx' => '__INDEX__', 'item' => ['product_id'=>'','qty'=>'','unit_price'=>''], 'products' => $products])
</template>
@endsection

@push('scripts')
<script>
const products = @json($products->map(fn($p) => ['id'=>$p->id,'price'=>$p->default_price,'name'=>$p->name]));

function formatRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function recalc() {
    let subtotal = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.line-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.line-price')?.value) || 0;
        const line = qty * price;
        subtotal += line;
        const lt = row.querySelector('.line-total');
        if (lt) lt.textContent = formatRp(line);
    });
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const isTax = document.getElementById('is_taxable').checked;
    const taxPct = parseFloat(document.getElementById('tax_percent').value) || 0;
    const base = Math.max(0, subtotal - discount);
    const tax = isTax ? base * (taxPct / 100) : 0;
    const grand = base + tax;
    document.getElementById('sumSubtotal').textContent = formatRp(subtotal);
    document.getElementById('sumTax').textContent = formatRp(tax);
    document.getElementById('sumGrand').textContent = formatRp(grand);
}

function bindRow(row) {
    row.querySelector('.line-product')?.addEventListener('change', function() {
        const p = products.find(x => x.id == this.value);
        if (p) row.querySelector('.line-price').value = p.price;
        recalc();
    });
    row.querySelectorAll('.line-qty,.line-price').forEach(el => el.addEventListener('input', recalc));
    row.querySelector('.remove-line')?.addEventListener('click', () => {
        if (document.querySelectorAll('#itemsBody tr').length > 1) {
            row.remove();
            reindexRows();
            recalc();
        }
    });
}

function reindexRows() {
    document.querySelectorAll('#itemsBody tr').forEach((row, i) => {
        row.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/items\[\d+\]/, `items[${i}]`);
        });
    });
}

let lineIndex = document.querySelectorAll('#itemsBody tr').length;
document.getElementById('addLine').addEventListener('click', () => {
    const html = document.getElementById('lineTemplate').innerHTML.replace(/__INDEX__/g, lineIndex++);
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    bindRow(document.querySelector('#itemsBody tr:last-child'));
    reindexRows();
});

document.querySelectorAll('#itemsBody tr').forEach(bindRow);
document.getElementById('discount').addEventListener('input', recalc);
document.getElementById('is_taxable').addEventListener('change', recalc);
document.getElementById('tax_percent').addEventListener('input', recalc);

function syncPayment() {
    const method = document.getElementById('payment_method').value;
    document.getElementById('cashAccountWrap').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('dueDateWrap').style.display = method === 'credit' ? 'block' : 'none';
}
document.getElementById('payment_method').addEventListener('change', syncPayment);
syncPayment();
recalc();
</script>
@endpush
