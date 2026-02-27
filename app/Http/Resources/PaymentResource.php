<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PaymentResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'method' => $this->payment_method->value,  // Enum → string
            'reference_number' => $this->reference_number,
            'paid_at' => $this->paid_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
