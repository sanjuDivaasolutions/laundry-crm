<?php

namespace App\Policies;

use App\Models\SalesInvoice;
use App\Models\User;

class SalesInvoicePolicy
{
    /**
     * Determine whether the user can view any sales invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the sales invoice.
     */
    public function view(User $user, SalesInvoice $invoice): bool
    {
        // Users can view invoices from their own company
        if ($user->company_id && $invoice->company_id) {
            return $user->company_id === $invoice->company_id ||
                   $user->hasPermission('manage-orders') ||
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can create sales invoices.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the sales invoice.
     */
    public function update(User $user, SalesInvoice $invoice): bool
    {
        // Users can update invoices from their own company
        if ($user->company_id && $invoice->company_id) {
            return $user->company_id === $invoice->company_id ||
                   $user->hasPermission('manage-orders') ||
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the sales invoice.
     */
    public function delete(User $user, SalesInvoice $invoice): bool
    {
        // Users can delete invoices from their own company
        if ($user->company_id && $invoice->company_id) {
            return $user->company_id === $invoice->company_id ||
                   $user->hasPermission('manage-orders') ||
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update payment status.
     */
    public function updatePaymentStatus(User $user, SalesInvoice $invoice): bool
    {
        // Users can update payment status of invoices from their own company
        if ($user->company_id && $invoice->company_id) {
            return $user->company_id === $invoice->company_id ||
                   $user->hasPermission('manage-orders') ||
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }
}
