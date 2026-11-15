@extends('layouts.admin')

@section('title', 'Edit Sale')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Edit Sale</h2>
    <p class="text-sm text-slate-500 mt-1">{{ $sale->invoice_number }}</p>
</div>

<div class="panel p-5 max-w-4xl">
    <form action="{{ route('sales.update', $sale) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                <select name="customer_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">— Walk-in —</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name</label>
                <input type="text" name="customer_name" value="{{ $sale->customer_name }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="partial" {{ ($sale->payment_status ?? '') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ ($sale->payment_status ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ ($sale->payment_status ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
        </div>

        {{-- Products --}}
        <div class="mb-5">
            <h3 class="font-semibold text-slate-800 mb-2">Products</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full text-sm" id="items-table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 w-20">Qty</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 w-28">Price</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 w-28">Amount</th>
                            <th class="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        @forelse($sale->items as $item)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">
                                    <input type="text" name="product_names[]" value="{{ $item->product_name }}" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm" required>
                                </td>
                                <td class="px-3 py-2"><input type="number" name="qtys[]" value="{{ $item->quantity }}" min="0.01" step="0.01" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm text-right qty" oninput="calcRow(this)"></td>
                                <td class="px-3 py-2"><input type="number" name="prices[]" value="{{ $item->unit_price }}" step="0.01" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm text-right price" oninput="calcRow(this)"></td>
                                <td class="px-3 py-2 text-right amount font-medium">{{ number_format($item->line_total, 0) }}</td>
                                <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 text-sm">✕</button></td>
                            </tr>
                        @empty
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2"><input type="text" name="product_names[]" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm" placeholder="Product name"></td>
                                <td class="px-3 py-2"><input type="number" name="qtys[]" value="1" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm text-right qty" oninput="calcRow(this)"></td>
                                <td class="px-3 py-2"><input type="number" name="prices[]" value="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm text-right price" oninput="calcRow(this)"></td>
                                <td class="px-3 py-2 text-right amount font-medium">0</td>
                                <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 text-sm">✕</button></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <button type="button" onclick="addRow()" class="mt-2 text-sm text-blue-600 font-medium">+ Add Product</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Total</label>
                <input type="text" id="total-display" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-slate-50 font-semibold" readonly value="{{ number_format($sale->total, 0) }}">
                <input type="hidden" name="total" id="total-input" value="{{ $sale->total }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Paid</label>
                <input type="number" name="paid" value="{{ $sale->paid }}" step="0.01" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary-soft">Update Sale</button>
            <a href="{{ route('sales.show', $sale) }}" class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg">Cancel</a>
        </div>
    </form>
</div>

<script>
function addRow() {
    const tbody = document.getElementById('items-body');
    const firstRow = tbody.rows[0];
    const newRow = firstRow.cloneNode(true);
    newRow.querySelectorAll('input').forEach(input => {
        if (input.name.includes('qty')) input.value = 1;
        else if (input.name.includes('price')) input.value = 0;
        else input.value = '';
    });
    newRow.querySelector('.amount').textContent = '0';
    tbody.appendChild(newRow);
}
function removeRow(btn) {
    const tbody = document.getElementById('items-body');
    if (tbody.rows.length > 1) {
        btn.closest('tr').remove();
        updateTotal();
    }
}
function calcRow(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    row.querySelector('.amount').textContent = (qty * price).toLocaleString();
    updateTotal();
}
function updateTotal() {
    let total = 0;
    document.querySelectorAll('#items-body tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        total += qty * price;
    });
    document.getElementById('total-display').value = total.toLocaleString();
    document.getElementById('total-input').value = total;
}
</script>
@endsection