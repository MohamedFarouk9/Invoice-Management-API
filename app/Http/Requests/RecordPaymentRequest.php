<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Business logic validation (amount <= remaining balance) happens in Service.
 */
class RecordPaymentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user() !== null;
    }


    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01|max:999999.99|decimal:0,2',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card',
            'reference_number' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be greater than 0.',
            'amount.max' => 'Payment amount cannot exceed 999999.99.',

            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Payment method must be one of: cash, bank_transfer, credit_card.',

            'reference_number.max' => 'Reference number cannot exceed 100 characters.',
        ];
    }
}
