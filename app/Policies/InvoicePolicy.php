<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;

/**
 * Invoice Policy - Authorization rules for invoice operations.
 * Policies check if a user can perform an action on a resource.
 */

class InvoicePolicy
{

  /**
     * Can user create an invoice for this contract?
     */
    public function create(User $user, Contract $contract): bool
    {
        // User can only create invoices for contracts in their tenant
        return $user->tenant_id === $contract->tenant_id;
    }

   /**
     * Can user view this invoice?
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // User can only view invoices in their tenant
        return $user->tenant_id === $invoice->contract->tenant_id;
    }

    /**
     * Can user list invoices for this contract?
     */
    public function viewAny(User $user, Contract $contract): bool
    {
        // User can only list invoices for contracts in their tenant
        return $user->tenant_id === $contract->tenant_id;
    }

    /**
     * Can user update this invoice?
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Can only update invoices in user's tenant
        return $user->tenant_id === $invoice->contract->tenant_id;
    }

    /**
     * Can user record a payment on this invoice?
     */

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        // User can only record payments for invoices in their tenant
        return $user->tenant_id === $invoice->contract->tenant_id;
    }

    /**
     * Can user delete this invoice?
     */

    public function delete(User $user, Invoice $invoice): bool
    {
        // Can only delete invoices in user's tenant
        return $user->tenant_id === $invoice->contract->tenant_id;
    }
}
