<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{

    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }


    public function rules(): array
    {
        return [
            'contract_id' => 'required|integer|exists:contracts,id',
            'due_date' => 'required|date|after:today',
        ];
    }

    /**
     * Get custom error messages.
     *
     * Customize validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contract_id.required' => 'Contract ID is required.',
            'contract_id.integer' => 'Contract ID must be a valid integer.',
            'contract_id.exists' => 'The selected contract does not exist.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date (YYYY-MM-DD).',
            'due_date.after' => 'Due date must be in the future.',
        ];
    }


}
