<tr>
    <td>
        <select name="items[{{ $idx }}][product_id]" class="form-select form-select-sm line-product" required>
            <option value="">— Select —</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" @selected(($item['product_id'] ?? '') == $p->id)>{{ $p->code }} — {{ $p->name }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" name="items[{{ $idx }}][qty]" class="form-control form-control-sm line-qty text-end" step="0.0001" min="0.0001" value="{{ $item['qty'] ?? '' }}" required></td>
    <td><input type="number" name="items[{{ $idx }}][unit_price]" class="form-control form-control-sm line-price text-end" step="1" min="0" value="{{ $item['unit_price'] ?? '' }}" required></td>
    <td class="text-end line-total align-middle">Rp 0</td>
    <td><button type="button" class="btn btn-sm btn-outline-danger remove-line">&times;</button></td>
</tr>
