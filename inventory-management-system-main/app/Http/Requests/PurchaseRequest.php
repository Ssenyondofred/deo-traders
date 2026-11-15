<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('purchases.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'purchase_date' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'payment_method' => ['required', Rule::in([PaymentMethod::Cash->value, PaymentMethod::Credit->value])],
            'cash_bank_account_id' => [
                Rule::requiredIf($this->payment_method === PaymentMethod::Cash->value),
                'nullable',
                'exists:cash_bank_accounts,id',
            ],
            'due_date' => [
                Rule::requiredIf($this->payment_method === PaymentMethod::Credit->value),
                'nullable',
                'date',
            ],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_taxable' => $this->boolean('is_taxable'),
            'discount' => $this->input('discount', 0),
        ]);
    }
}
