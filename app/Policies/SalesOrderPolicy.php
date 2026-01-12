<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalesOrder;
use Illuminate\Auth\Access\Response;

class SalesOrderPolicy
{
    /**
     * Determine whether the user can view any sales orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the sales order.
     */
    public function view(User $user, SalesOrder $order): bool
    {
        // Users can view orders from their own company
        if ($user->company_id && $order->company_id) {
            return $user->company_id === $order->company_id || 
                   $user->hasPermission('manage-orders') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can create sales orders.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the sales order.
     */
    public function update(User $user, SalesOrder $order): bool
    {
        // Users can update orders from their own company
        if ($user->company_id && $order->company_id) {
            return $user->company_id === $order->company_id || 
                   $user->hasPermission('manage-orders') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the sales order.
     */
    public function delete(User $user, SalesOrder $order): bool
    {
        // Users can delete orders from their own company
        if ($user->company_id && $order->company_id) {
            return $user->company_id === $order->company_id || 
                   $user->hasPermission('manage-orders') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can convert the sales order to invoice.
     */
    public function convertToInvoice(User $user, SalesOrder $order): bool
    {
        // Only confirmed orders can be converted
        if ($order->status !== 'confirmed') {
            return false;
        }

        // Users can convert orders from their own company
        if ($user->company_id && $order->company_id) {
            return $user->company_id === $order->company_id || 
                   $user->hasPermission('manage-orders') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the sales order status.
     */
    public function updateStatus(User $user, SalesOrder $order): bool
    {
        // Users can update status of orders from their own company
        if ($user->company_id && $order->company_id) {
            return $user->company_id === $order->company_id || 
                   $user->hasPermission('manage-orders') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-orders') || $user->isAdmin();
    }
}