<?php

namespace App\Services;

use App\DTOs\CreateInvoiceDTO;
use App\DTOs\RecordPaymentDTO;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Invoices\InvoiceRepositoryInterface;
use App\Repositories\Payments\PaymentRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Invoice Service - All invoice business logic.
 *
 * Responsibilities:
 * - Validate business rules (contract active, amount valid, etc.)
 * - Calculate taxes
 * - Generate invoice numbers
 * - Manage payments and status transitions
 */
class InvoiceService
{
    public function __construct(
        private ContractRepositoryInterface $contractRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        private PaymentRepositoryInterface $paymentRepo,
        private TaxService $taxService,
    ) {}

    /**
     * Create an invoice from a contract.
     *
     * Business rules:
     * - Contract must exist and be ACTIVE
     * - Contract must belong to user's tenant
     * - Invoice number is auto-generated: INV-{TENANT}-{YYYYMM}-{SEQUENCE}
     * - Tax is calculated via TaxService
     * - Total = subtotal + tax
     */
    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        return DB::transaction(function () use ($dto) {
            // VALIDATION: Contract exists
            $contract = $this->contractRepo->findById($dto->contract_id);
            if (!$contract) {
                throw new \Exception('Contract not found', 404);
            }

            // VALIDATION: Contract is ACTIVE
            if ($contract->status !== 'active') {
                throw new \Exception(
                    'Cannot create invoice for ' . $contract->status . ' contract',
                    422
                );
            }

            // VALIDATION: Contract belongs to user's tenant
            if ($contract->tenant_id !== $dto->tenant_id) {
                throw new \Exception('Unauthorized', 403);
            }

            // CALCULATION: Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber(
                $dto->tenant_id,
                $dto->contract_id
            );

            // CALCULATION: Calculate taxes
            $subtotal = (float) $contract->rent_amount;
            $taxAmount = $this->taxService->calculate($subtotal);
            $total = $subtotal + $taxAmount;

            // CREATION: Create invoice via repository
            $invoice = $this->invoiceRepo->create([
                'tenant_id' => $dto->tenant_id,
                'contract_id' => $dto->contract_id,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => InvoiceStatus::PENDING->value,
                'due_date' => $dto->due_date,
            ]);

            return $invoice;
        });
    }

    /**
     * Record a payment against an invoice.
     *
     * Business rules: provided in task description
     * - Invoice must exist and not be cancelled
     * - Amount must be > 0
     * - Amount cannot exceed remaining balance
     * - Status updates automatically:
     *   - partial payment: status = partially_paid
     *   - full payment: status = paid, paid_at = now()
     * - All operations wrapped in transaction for consistency
     */
    public function recordPayment(RecordPaymentDTO $dto): Payment
    {
        return DB::transaction(function () use ($dto) {
            // VALIDATION: Invoice exists
            $invoice = $this->invoiceRepo->findById($dto->invoice_id);
            if (!$invoice) {
                throw new \Exception('Invoice not found', 404);
            }

            // VALIDATION: Invoice belongs to user's tenant
            if ($invoice->tenant_id !== $dto->tenant_id) {
                throw new \Exception('Unauthorized', 403);
            }

            // VALIDATION: Invoice can receive payments
            if ($invoice->status === InvoiceStatus::CANCELLED->value) {
                throw new \Exception('Cannot pay cancelled invoice', 422);
            }

            // VALIDATION: Cannot pay already paid invoice
            if ($invoice->status === InvoiceStatus::PAID->value) {
                throw new \Exception('Invoice already paid', 422);
            }

            // VALIDATION: Amount is positive
            if ($dto->amount <= 0) {
                throw new \Exception('Payment amount must be greater than 0', 422);
            }

            // CALCULATION: Remaining balance
            $totalPaid = $this->paymentRepo->getTotalPaidForInvoice($dto->invoice_id);
            $remainingBalance = $invoice->total - $totalPaid;

            // VALIDATION: Cannot overpay
            if ($dto->amount > $remainingBalance) {
                throw new \Exception(
                    "Payment amount ({$dto->amount}) exceeds remaining balance ({$remainingBalance})",
                    422
                );
            }

            // CREATION: Create payment via repository
            $payment = $this->paymentRepo->create([
                'invoice_id' => $dto->invoice_id,
                'amount' => $dto->amount,
                'payment_method' => $dto->payment_method,
                'reference_number' => $dto->reference_number,
                'paid_at' => now(),
            ]);

            // STATUS UPDATE: Determine new invoice status
            $newTotalPaid = $totalPaid + $dto->amount;
            if ($newTotalPaid >= $invoice->total) {
                // Full payment made
                $this->invoiceRepo->markAsPaid($dto->invoice_id);
                $invoice->status = InvoiceStatus::PAID;
                $invoice->paid_at = now();
            } elseif ($newTotalPaid > 0) {
                // Partial payment made
                $this->invoiceRepo->updateStatus(
                    $dto->invoice_id,
                    InvoiceStatus::PARTIALLY_PAID->value
                );
                $invoice->status = InvoiceStatus::PARTIALLY_PAID;
            }

            // EVENTS: Dispatch event (optional for Batch 8)
            // event(new PaymentRecorded($payment, $invoice));

            return $payment;
        });
    }

    /**
     * Get financial summary for a contract.
     */
    public function getContractSummary(int $contractId, int $tenantId): array
    {
        // Get all invoices for contract
        $invoices = $this->invoiceRepo->getByContractId($contractId);

        // Verify contract belongs to tenant (security)
        if ($invoices->first()?->contract->tenant_id !== $tenantId) {
            throw new \Exception('Unauthorized', 403);
        }

        // Calculate totals
        $totalInvoiced = (float) $invoices->sum('total');
        $totalPaid = (float) $invoices->sum(function ($invoice) {
            return $invoice->payments->sum('amount');
        });
        $outstandingBalance = $totalInvoiced - $totalPaid;

        return [
            'contract_id' => $contractId,
            'total_invoiced' => round($totalInvoiced, 2),
            'total_paid' => round($totalPaid, 2),
            'outstanding_balance' => round($outstandingBalance, 2),
            'invoices_count' => $invoices->count(),
            'latest_invoice_date' => $invoices->first()?->created_at,
        ];
    }

    /**
     * Generate unique invoice number.
     *
     * Format: INV-{TENANT_ID:3}-{YYYYMM}-{SEQUENCE:4}
     * Example: INV-001-202602-0042
     *
     * Sequence increments per month per tenant.
     * This ensures numbers are meaningful and sequential.
     */
    private function generateInvoiceNumber(int $tenantId, int $contractId): string
    {
        $yearMonth = now()->format('Ym');  //"202602"
        $sequence = $this->invoiceRepo->getLastSequenceForMonth($tenantId, $yearMonth);
        $nextSequence = $sequence + 1;

        return sprintf(
            'INV-%03d-%s-%04d',
            $tenantId,
            $yearMonth,
            $nextSequence
        );
    }
}


