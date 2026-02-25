<?php

namespace App\DTOs;

/**
 * Data Transfer Object for creating an invoice.
 * Immutable - cannot be changed after creation.
 */
readonly class CreateInvoiceDTO
{
    public function __construct(
        public int $contract_id,
        public int $tenant_id,
        public string $due_date,
    ) {}

    /**
     * Create DTO from validated Form Request data.
     */
    public static function fromRequest(array $validated, int $tenant_id): self
    {
        return new self(
            contract_id: (int) $validated['contract_id'],
            tenant_id: $tenant_id,
            due_date: $validated['due_date'],
        );
    }

    /**
     * Convert to array for database operations.
     */
    public function toArray(): array
    {
        return [
            'contract_id' => $this->contract_id,
            'tenant_id' => $this->tenant_id,
            'due_date' => $this->due_date,
        ];
    }
}
