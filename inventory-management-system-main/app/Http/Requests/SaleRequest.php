<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sales.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'sale_date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
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
            'qty' => ['required', 'numeric', 'min:0.0001'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
