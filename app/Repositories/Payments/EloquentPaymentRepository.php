<?php

namespace App\Repositories\Payments;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent Payment Repository.
 * Implements PaymentRepositoryInterface using Eloquent.
 */
class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment
    {
        return Payment::find($id);
    }

    public function findByIdOrFail(int $id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function getByInvoiceId(int $invoiceId): Collection
    {
        return Payment::where('invoice_id', $invoiceId)
            ->orderBy('paid_at', 'desc')
            ->get();
    }

    public function getTotalPaidForInvoice(int $invoiceId): float
    {
        return (float) Payment::where('invoice_id', $invoiceId)
            ->sum('amount');
    }

    public function getByTenant(int $tenantId): Collection
    {
        return Payment::whereHas('invoice', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->orderBy('paid_at', 'desc')
            ->get();
    }

    public function getByMethod(int $tenantId, string $method): Collection
    {
        return Payment::where('payment_method', $method)
            ->whereHas('invoice', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->orderBy('paid_at', 'desc')
            ->get();
    }

    public function delete(int $id): bool
    {
        return (bool) Payment::destroy($id);
    }
}
