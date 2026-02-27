<?php

namespace App\Http\Controllers;

use App\DTOs\CreateInvoiceDTO;
use App\DTOs\RecordPaymentDTO;
use App\Http\Requests\RecordPaymentRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\ContractSummaryResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaymentResource;
use App\Models\Contract;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Store a newly created invoice for a contract.
     * POST /api/contracts/{contract}/invoices
     */
    public function store(StoreInvoiceRequest $request, Contract $contract)
    {
        $dto = CreateInvoiceDTO::fromRequest($request->validated(), $contract->tenant_id);

        $invoice = $this->invoiceService->createInvoice($dto);

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'subtotal' => (float) $invoice->subtotal,
            'tax_amount' => (float) $invoice->tax_amount,
            'total' => (float) $invoice->total,
            'status' => $invoice->status?->value,
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'paid_at' => $invoice->paid_at?->toIso8601String(),
            'created_at' => $invoice->created_at?->toIso8601String(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display all invoices for a contract (with pagination & filtering).
     *
     * GET /api/contracts/{contract}/invoices?status=pending&per_page=20
     */
    public function index(Contract $contract)
    {
        $invoices = $contract->invoices()
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('date_from'), function ($query) {
                $query->where('created_at', '>=', request('date_from'));
            })
            ->when(request('date_to'), function ($query) {
                $query->where('created_at', '<=', request('date_to'));
            })
            ->latest('created_at')
            ->paginate(request('per_page', 20));

        return response()->json([
            'data' => $invoices->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'subtotal' => (float) $invoice->subtotal,
                'tax_amount' => (float) $invoice->tax_amount,
                'total' => (float) $invoice->total,
                'status' => $invoice->status?->value,
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'paid_at' => $invoice->paid_at?->toIso8601String(),
                'created_at' => $invoice->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'total' => $invoices->total(),
                'count' => $invoices->count(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified invoice with all details.
     * 
     * GET /api/invoices/{invoice}
     */
    public function show(Invoice $invoice)
    {
        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'subtotal' => (float) $invoice->subtotal,
            'tax_amount' => (float) $invoice->tax_amount,
            'total' => (float) $invoice->total,
            'status' => $invoice->status?->value,
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'paid_at' => $invoice->paid_at?->toIso8601String(),
            'created_at' => $invoice->created_at?->toIso8601String(),
            'updated_at' => $invoice->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Record a payment against an invoice.
     * POST /api/invoices/{invoice}/payments
     */

    public function recordPayment(RecordPaymentRequest $request, Invoice $invoice)
    {
        $dto = RecordPaymentDTO::fromRequest(
            $request->validated(),
            $invoice->id,
            $invoice->tenant_id
        );
        $payment = $this->invoiceService->recordPayment($dto);

        return response()->json([
            'id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method?->value,
            'reference_number' => $payment->reference_number,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'created_at' => $payment->created_at?->toIso8601String(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get financial summary for a contract.
     * 
     * GET /api/contracts/{contract}/summary
     */

    public function summary(Contract $contract)
    {
        $summary = $this->invoiceService->getContractSummary(
            $contract->id,
            $contract->tenant_id
        );

        return response()->json($summary);
    }
}
