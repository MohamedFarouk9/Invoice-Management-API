<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;

/**
 * Invoice Policy - Authorization rules for invoice operations.
 * Policies check if a user can perform an action on a resource.
 *
 * Current logic: User must belong to the same tenant as the contract/invoice.
 */

class InvoicePolicy
{

    /**
     * Can user create an invoice for this contract?
     */
    public function create(mixed $user, Contract $contract): bool
    {
        return $user && $user->tenant_id === $contract->tenant_id;
    }

    /**
     * Can user view this invoice?
     */
    public function view(mixed $user, Invoice $invoice): bool
    {
        return $user && $user->tenant_id === $invoice->tenant_id;
    }

    /**
     * Can user list invoices for this contract?
     */
    public function viewAny(mixed $user, Contract $contract): bool
    {
        return $user && $user->tenant_id === $contract->tenant_id;
    }

    /**
     * Can user update this invoice?
     */
    public function update(mixed $user, Invoice $invoice): bool
    {
        return $user && $user->tenant_id === $invoice->tenant_id;
    }

    /**
     * Can user record a payment on this invoice?
     */

    public function recordPayment(mixed $user, Invoice $invoice): bool
    {
        return $user && $user->tenant_id === $invoice->tenant_id;
    }

    /**
     * Can user delete this invoice?
     */

    public function delete(mixed $user, Invoice $invoice): bool
    {
        return $user && $user->tenant_id === $invoice->tenant_id;
    }
}
