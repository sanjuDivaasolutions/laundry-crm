<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 12/02/25, 4:39 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\Company;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public static function updateSalesOrderTotals($obj, string $type = 'sales'): void
    {
        $subTotal = 0;

        $items = $obj->items()->get();
        foreach ($items as $item) {
            $subTotal += $item->amount;
        }

        $mixTotal = $subTotal;

        $taxTotal = $obj->taxes()->sum('amount');
        $mixTotal = $mixTotal + $taxTotal;

        if ($type == 'sales') {
            // Calculate commission_total if commission is set
            $commissionTotal = 0;
            if ($obj->commission && $obj->commission > 0) {
                // Calculate backwards: sub_total includes commission
                // base_amount = sub_total / (1 + commission/100)
                // commission_total = sub_total - base_amount
                $baseAmount = $subTotal / (1 + ($obj->commission / 100));
                $commissionTotal = round($subTotal - $baseAmount, 2);
            }
            $obj->commission_total = $commissionTotal;
        }

        $obj->sub_total = $subTotal;
        $obj->tax_total = $taxTotal;
        $obj->grand_total = $mixTotal;
        $obj->save();

        if ($obj instanceof SalesInvoice) {
            $obj->syncPaymentStatus();
        }
    }

    public static function updatePurchaseOrderTotals($obj): void
    {
        $subTotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        $items = $obj->items()->get();
        foreach ($items as $item) {
            $subTotal += $item->amount;
        }

        $mixTotal = $subTotal - $discountTotal;
        $mixTotal = $mixTotal + $taxTotal;

        $obj->sub_total = $subTotal;
        $obj->tax_total = $taxTotal;
        $obj->grand_total = $mixTotal;
        $obj->save();
    }

    /**
     * Create a new sales order with commission calculation
     */
    public function createSalesOrder(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $salesOrder = SalesOrder::create($data);

            // Calculate and create commission if agent is assigned
            if (isset($data['agent_id']) && $data['agent_id']) {
                $this->calculateCommission($salesOrder, (int) $data['agent_id']);
            }

            return $salesOrder;
        });
    }

    /**
     * Update sales order and recalculate commission if needed
     */
    public function updateSalesOrder(SalesOrder $salesOrder, array $data): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $data) {
            $salesOrder->update($data);

            // Recalculate commission if agent or amount changed
            if (isset($data['agent_id']) || isset($data['grand_total'])) {
                $this->recalculateCommission($salesOrder);
            }

            return $salesOrder->fresh();
        });
    }

    /**
     * Convert sales order to invoice
     */
    public function convertToInvoice(SalesOrder $salesOrder, array $invoiceData = []): SalesInvoice
    {
        return DB::transaction(function () use ($salesOrder, $invoiceData) {
            $invoiceData = array_merge([
                'sales_order_id' => $salesOrder->id,
                'buyer_id' => $salesOrder->buyer_id,
                'company_id' => $salesOrder->company_id,
                'user_id' => $salesOrder->user_id,
                'order_type' => $salesOrder->order_type,
                'sub_total' => $salesOrder->sub_total,
                'tax_total' => $salesOrder->tax_total,
                'grand_total' => $salesOrder->grand_total,
                'tax_rate' => $salesOrder->tax_rate,
                'is_taxable' => $salesOrder->is_taxable,
                'date' => now()->format(config('project.date_format')),
                'due_date' => now()->addDays(30)->format(config('project.date_format')),
                'payment_status' => 'pending',
                'status' => 'draft',
            ], $invoiceData);

            $invoice = SalesInvoice::create($invoiceData);

            // Copy order items to invoice items
            foreach ($salesOrder->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'tax_total' => $item->tax_total,
                    'sub_total' => $item->sub_total,
                    'grand_total' => $item->grand_total,
                ]);
            }

            // Update order status
            $salesOrder->update(['status' => 'converted']);

            // Calculate commission for the invoice
            if ($salesOrder->agent_id) {
                $this->calculateCommission($invoice, (int) $salesOrder->agent_id);
            }

            return $invoice;
        });
    }

    /**
     * Calculate and create commission for an order or invoice
     */
    protected function calculateCommission(SalesOrder|SalesInvoice $model, int $agentId): AgentCommission
    {
        $agent = Agent::findOrFail($agentId);
        $commissionAmount = $agent->calculateCommission((float) $model->grand_total);

        return AgentCommission::create([
            'agent_id' => $agentId,
            'commissionable_type' => get_class($model),
            'commissionable_id' => $model->id,
            'commission_amount' => $commissionAmount,
            'commission_rate' => $agent->commission_rate,
            'commission_type' => $agent->commission_type,
            'status' => 'pending',
            'commission_date' => now(),
        ]);
    }

    /**
     * Recalculate commission for a model
     */
    protected function recalculateCommission(SalesOrder|SalesInvoice $model): void
    {
        // Delete existing pending commissions
        $model->commissions()->pending()->delete();

        // Create new commission if agent is assigned
        if ($model->agent_id) {
            $this->calculateCommission($model, (int) $model->agent_id);
        }
    }

    /**
     * Get order statistics for a company
     */
    public function getOrderStatistics(Company $company, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = SalesOrder::where('company_id', $company->id);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $orders = $query->get();

        return [
            'total_orders' => $orders->count(),
            'total_amount' => (float) $orders->sum('grand_total'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'confirmed_orders' => $orders->where('status', 'confirmed')->count(),
            'converted_orders' => $orders->where('status', 'converted')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'average_order_value' => $orders->count() > 0 ? (float) $orders->sum('grand_total') / $orders->count() : 0,
        ];
    }

    /**
     * Get commission summary for agents
     */
    public function getCommissionSummary(Company $company, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AgentCommission::whereHasMorph(
            'commissionable',
            [SalesOrder::class, SalesInvoice::class],
            function ($query) use ($company) {
                $query->where('company_id', $company->id);
            }
        );

        if ($startDate && $endDate) {
            $query->whereBetween('commission_date', [$startDate, $endDate]);
        }

        $commissions = $query->get();

        return [
            'total_commissions' => $commissions->count(),
            'total_commission_amount' => (float) $commissions->sum('commission_amount'),
            'pending_commissions' => $commissions->where('status', 'pending')->count(),
            'pending_amount' => (float) $commissions->where('status', 'pending')->sum('commission_amount'),
            'approved_commissions' => $commissions->where('status', 'approved')->count(),
            'approved_amount' => (float) $commissions->where('status', 'approved')->sum('commission_amount'),
            'paid_commissions' => $commissions->where('status', 'paid')->count(),
            'paid_amount' => (float) $commissions->where('status', 'paid')->sum('commission_amount'),
        ];
    }

    /**
     * Approve pending commissions
     */
    public function approveCommissions(array $commissionIds, ?User $user = null): int
    {
        $approvedCount = 0;

        DB::transaction(function () use ($commissionIds, $user, &$approvedCount) {
            $commissions = AgentCommission::pending()
                ->whereIn('id', $commissionIds)
                ->get();

            foreach ($commissions as $commission) {
                if ($commission->approve($user)) {
                    $approvedCount++;
                }
            }
        });

        return $approvedCount;
    }

    /**
     * Mark commissions as paid
     */
    public function markCommissionsAsPaid(array $commissionIds, ?User $user = null): int
    {
        $paidCount = 0;

        DB::transaction(function () use ($commissionIds, $user, &$paidCount) {
            $commissions = AgentCommission::approved()
                ->whereIn('id', $commissionIds)
                ->get();

            foreach ($commissions as $commission) {
                if ($commission->markAsPaid($user)) {
                    $paidCount++;
                }
            }
        });

        return $paidCount;
    }

    /**
     * Get outstanding invoices for a buyer
     */
    public function getOutstandingInvoicesForBuyer(SalesInvoice $currentInvoice): array
    {
        $outstandingInvoices = SalesInvoice::where('buyer_id', $currentInvoice->buyer_id)
            ->where('id', '!=', $currentInvoice->id)
            ->whereIn('payment_status', ['pending', 'partial'])
            ->with(['payments'])
            ->get();

        $invoices = $outstandingInvoices->map(function ($invoice) {
            $paidAmount = $invoice->payments->sum('amount');
            $pendingAmount = $invoice->grand_total - $paidAmount;

            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'date' => $invoice->date,
                'grand_total' => $invoice->grand_total,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'payment_status' => $invoice->payment_status,
                'status_label' => ucfirst($invoice->payment_status),
            ];
        });

        return [
            'invoices' => $invoices,
            'total' => $invoices->sum('pending_amount'),
        ];
    }
}
