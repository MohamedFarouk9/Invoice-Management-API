<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ContractResource extends JsonResource
{
  
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_name' => $this->unit_name,
            'customer_name' => $this->customer_name,
            'rent_amount' => (float) $this->rent_amount,
            'status' => $this->status->value,  // Enum → string
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
