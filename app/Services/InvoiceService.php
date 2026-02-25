<?php

namespace App\Services;

use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Invoices\InvoiceRepositoryInterface;
use App\Repositories\Payments\PaymentRepositoryInterface;

class InvoiceService
{
    public function __construct(
        private ContractRepositoryInterface $contractRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        // private PaymentRepositoryInterface $paymentRepo,
    ) {}

    public function createInvoice($dto)
    {
        // Service doesn't know/care about database implementation
        // Just uses repository interface
        $contract = $this->contractRepo->findByIdOrFail($dto->contract_id);

        $invoice = $this->invoiceRepo->create([
            'contract_id' => $contract->id,
            'subtotal' => $contract->rent_amount,
            'total' => $contract->rent_amount + 175,
        ]);

        return $invoice;
    }
}


/**
 *
 * InvoiceServiceCreating an invoice from a contract (validate contract is active,
 * calculate taxes via TaxService, generate invoice number,
 *  persist via Repository) • Recording a payment against an invoice (validate amount, update invoice status,
 *  persist via Repository) • Getting financial summary for a contract (total invoiced, total paid, outstanding balance)
 */
