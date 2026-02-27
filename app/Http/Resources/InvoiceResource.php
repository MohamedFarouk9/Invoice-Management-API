<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class InvoiceResource extends JsonResource
{
  
    public function toArray(Request $request): array
    {
        return [
            // Basic invoice data
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'status' => $this->status->value,  // Enum → string
            'due_date' => $this->due_date->format('Y-m-d'),

            // Timestamps
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Computed field: Remaining balance
            // Shows how much is left to pay
            'remaining_balance' => (float) (
                $this->total - $this->payments->sum('amount')
            ),

         
            'contract' => ContractResource::whenLoaded('contract', $this->contract),
            'payments' => PaymentResource::collection(
                $this->whenLoaded('payments', $this->payments)
            ),
        ];
    }

    /**
     * Add metadata to the response.
     * 
     * Useful for including additional information without polluting data.
     * 
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
