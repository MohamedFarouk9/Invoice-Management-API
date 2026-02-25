<?php

namespace App\DTOs;


readonly class RecordPaymentDTO
{
    public function __construct(
        public int $invoice_id,
        public int $tenant_id,
        public float $amount,
        public string $payment_method,
        public ?string $reference_number = null,
    ) {}


    public static function fromRequest(
        array $validated,
        int $invoiceId,
        int $tenant_id,
    ): self {
        return new self(
            invoice_id: $invoiceId,
            tenant_id: $tenant_id,
            amount: (float) $validated['amount'],
            payment_method: $validated['payment_method'],
            reference_number: $validated['reference_number'] ?? null,
        );
    }


    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'paid_at' => now(),
        ];
    }
}
