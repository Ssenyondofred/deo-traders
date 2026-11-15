<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('inventory.adjust') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'movement_date' => ['required', 'date'],
            'adjustment_type' => ['required', 'in:opening,adjustment_in,adjustment_out'],
            'direction' => ['required', 'in:in,out'],
            'qty' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['required_if:direction,in', 'nullable', 'numeric', 'min:0'],
            'batch_no' => ['nullable', 'string', 'max:50'],
            'notes' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Keterangan penggunaan stok wajib diisi.',
            'unit_cost.required_if' => 'Harga satuan wajib diisi untuk stok masuk.',
        ];
    }
}
