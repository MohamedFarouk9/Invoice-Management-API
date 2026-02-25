<?php

namespace App\Repositories\Invoices;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


interface InvoiceRepositoryInterface
{

    public function findById(int $id): ?Invoice;


    public function findByIdOrFail(int $id): Invoice;

    /**
     * Find invoice by invoice number.
     */
    public function findByNumber(string $invoiceNumber): ?Invoice;

    /**
     * Create a new invoice.
     */
    public function create(array $data): Invoice;

    /**
     * Update invoice.
     */
    public function update(int $id, array $data): Invoice;

    /**
     * Get invoices for a contract (with relationships).
     */
    public function getByContractId(int $contractId): Collection;


    public function getByContractIdPaginated(
        int $contractId,
        int $perPage = 20,
        ?string $status = null
    ): LengthAwarePaginator;

    /**
     * Get invoices for a tenant.
     */
    public function getByTenant(int $tenantId): Collection;

    /**
     * Get invoices by status.
     */
    public function getByStatus(int $tenantId, string $status): Collection;

    /**
     * Get the last invoice number sequence for a month.
     * Used to generate next invoice number.
     */
    public function getLastSequenceForMonth(int $tenantId, string $yearMonth): int;

    /**
     * Update invoice status.
     */
    public function updateStatus(int $id, string $status): Invoice;

    /**
     * Mark as paid (set paid_at timestamp).
     */
    public function markAsPaid(int $id): Invoice;

    /**
     * Delete invoice.
     */
    public function delete(int $id): bool;
}
