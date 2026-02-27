<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ContractSummaryResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'contract_id' => $this->contract_id,

            'total_invoiced' => (float) $this->total_invoiced,
            'total_paid' => (float) $this->total_paid,
            'outstanding_balance' => (float) $this->outstanding_balance,

            'invoices_count' => (int) $this->invoices_count,
            'latest_invoice_date' => $this->latest_invoice_date?->format('Y-m-d'),

            'payment_percentage' => $this->total_invoiced > 0
                ? round(($this->total_paid / $this->total_invoiced) * 100, 2)
                : 0,

            'outstanding_percentage' => $this->total_invoiced > 0
                ? round(($this->outstanding_balance / $this->total_invoiced) * 100, 2)
                : 0,
        ];
    }
}
