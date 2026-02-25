<?php

namespace App\Repositories\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice
    {
        return Invoice::with(['contract', 'payments'])->find($id);
    }

    public function findByIdOrFail(int $id): Invoice
    {
        return Invoice::with(['contract', 'payments'])->findOrFail($id);
    }

    public function findByNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::where('invoice_number', $invoiceNumber)
            ->with(['contract', 'payments'])
            ->first();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function update(int $id, array $data): Invoice
    {
        $invoice = $this->findByIdOrFail($id);
        $invoice->update($data);
        return $invoice->fresh(['contract', 'payments']);
    }

    public function getByContractId(int $contractId): Collection
    {
        return Invoice::where('contract_id', $contractId)
            ->with(['payments'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByContractIdPaginated(
        int $contractId,
        int $perPage = 20,
        ?string $status = null
    ): LengthAwarePaginator {
        $query = Invoice::where('contract_id', $contractId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['payments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByTenant(int $tenantId): Collection
    {
        return Invoice::where('tenant_id', $tenantId)
            ->with(['contract', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(int $tenantId, string $status): Collection
    {
        return Invoice::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->with(['contract', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLastSequenceForMonth(int $tenantId, string $yearMonth): int
    {
        // yearMonth format: "202602" for February 2026
        $lastInvoice = Invoice::where('tenant_id', $tenantId)
            ->where('created_at', '>=', "202{$yearMonth}-01") 
            ->latest('invoice_number')
            ->first();

        if (!$lastInvoice) {
            return 0;  // First invoice of the month
        }

        // Extract sequence from invoice number: INV-001-202602-0005
        $parts = explode('-', $lastInvoice->invoice_number);
        return (int) end($parts);
    }

    public function updateStatus(int $id, string $status): Invoice
    {
        return $this->update($id, ['status' => $status]);
    }

    public function markAsPaid(int $id): Invoice
    {
        return $this->update($id, [
            'status' => InvoiceStatus::PAID->value,
            'paid_at' => now(),
        ]);
    }

    public function delete(int $id): bool
    {
        return (bool) Invoice::destroy($id);
    }
}
