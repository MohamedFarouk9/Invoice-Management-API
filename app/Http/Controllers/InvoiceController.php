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
        $this->authorize('create', [Invoice::class, $contract]);

        $dto = CreateInvoiceDTO::fromRequest($request->validated(), auth()->user()->tenant_id);

        $invoice = $this->invoiceService->createInvoice($dto);

        return response()->json($invoice, Response::HTTP_CREATED);
    }

    /**
     * Display all invoices for a contract (with pagination & filtering).
     *
     * GET /api/contracts/{contract}/invoices?status=pending&per_page=20
     */
    public function index(Contract $contract)
    {
        // Can user view invoices for this contract?
        $this->authorize('viewAny', [Invoice::class, $contract]);

        $invoices = $contract->invoices()
            ->with(['payments', 'contract'])
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


        return InvoiceResource::collection($invoices);
    }

    /**
     * Display the specified invoice with all details.
     * 
     * GET /api/invoices/{invoice}
     */
    public function show(Invoice $invoice){
        $this->authorize('view', $invoice);

        $invoice->load(['contract', 'payments']); //prevent n+1

        return InvoiceResource::make($invoice);
    }

    /**
     * Record a payment against an invoice.
     * POST /api/invoices/{invoice}/payments
     */

    public function recordPayment(RecordPaymentRequest $request, Invoice $invoice) {
        $this->authorize('recordPayment', $invoice);

        $dto = RecordPaymentDTO::fromRequest(
            $request->validated(),
            $invoice->id,
            auth()->user()->tenant_id
        );
        $payment = $this->invoiceService->recordPayment($dto);

        // Step 4: Return response with 201 Created status
        return PaymentResource::make($payment)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Get financial summary for a contract.
     * 
     * GET /api/contracts/{contract}/summary
     */

    public function summary(Contract $contract)
    {
        // Step 1: Authorization - Can user view contract summary?
        $this->authorize('viewAny', [Invoice::class, $contract]);

        // Step 2: Get summary from Service
        $summary = $this->invoiceService->getContractSummary(
            $contract->id,
            auth()->user()->tenant_id
        );

        // Step 3: Return resource
        return ContractSummaryResource::make($summary);
    }
  
}

