<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('production.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'production_date' => ['required', 'date'],
            'batch_label' => ['nullable', 'string', 'max:100'],
            'raw_corn_qty' => ['required', 'numeric', 'min:0.0001'],
            'coal_qty' => ['required', 'numeric', 'min:0.0001'],
            'output_qty' => ['required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'raw_corn_qty.required' => 'Qty jagung mentah wajib diisi.',
            'coal_qty.required' => 'Qty batubara wajib diisi.',
            'output_qty.required' => 'Qty jagung kering wajib diisi.',
        ];
    }
}
