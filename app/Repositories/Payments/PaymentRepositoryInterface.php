<?php

namespace App\Repositories\Payments;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;


interface PaymentRepositoryInterface
{
    /**
     * Find payment by ID.
     */
    public function findById(int $id): ?Payment;

    /**
     * Find payment by ID or fail.
     */
    public function findByIdOrFail(int $id): Payment;

    /**
     * Create a new payment.
     */
    public function create(array $data): Payment;

    /**
     * Get all payments for an invoice.
     */
    public function getByInvoiceId(int $invoiceId): Collection;

    /**
     * Get total amount paid for an invoice.
     */
    public function getTotalPaidForInvoice(int $invoiceId): float;

    /**
     * Get payments for a tenant.
     */
    public function getByTenant(int $tenantId): Collection;

    /**
     * Get payments by method.
     */
    public function getByMethod(int $tenantId, string $method): Collection;

    /**
     * Delete payment.
     */
    public function delete(int $id): bool;
}
