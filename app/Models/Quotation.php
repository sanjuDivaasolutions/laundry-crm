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
 *  *  Last modified: 21/01/25, 5:34 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Attributes\DateAttribute;
use App\Attributes\ExpectedDeliveryDateAttribute;
use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    use HasAdvancedFilter, DateAttribute, ExpectedDeliveryDateAttribute, CompanyScopeTrait;

    protected $table = 'quotations';

    protected $casts = [
        'date'                   => 'date',
        'expected_delivery_date' => 'date',
    ];

    protected array $overrideOrderFields = [
        'sub_total_text'   => 'sub_total',
        'tax_total_text'   => 'tax_total',
        'grand_total_text' => 'grand_total',
    ];

    // orderable
    protected array $orderable = [
        'id',
        'order_no',
        'reference_no',
        'date',
        'sub_total',
        'tax_total',
        'grand_total',
        'expected_delivery_date',
    ];

    // filterable
    protected array $filterable = [
        'id',
        'order_no',
        'reference_no',
        'date',
        'sub_total',
        'tax_total',
        'grand_total',
        'expected_delivery_date',
        'remark',
    ];

    protected $fillable = [
        'order_no',
        'reference_no',
        'company_id',
        'buyer_id',
        'warehouse_id',
        'date',
        'sub_total',
        'tax_total',
        'grand_total',
        'remark',
        'expected_delivery_date',
        'state_id',
        'user_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(QuotationStatus::class);
    }

    public function status(): HasOne
    {
        return $this->hasOne(QuotationStatus::class)->where('active', 1);
    }

    /**
     * Convert quotation to sales order
     */
    public function convertToSalesOrder(array $data = [])
    {
        $salesOrder = null;
        \App\Services\DatabaseService::executeTransaction(function () use ($data, &$salesOrder) {
            // Create sales order from quotation data
            $salesOrder = SalesOrder::create([
                'company_id' => $this->company_id,
                'so_number' => $data['so_number'] ?? $this->generateSalesOrderNumber(),
                'quotation_no' => $this->order_no,
                'reference_no' => $data['reference_no'] ?? $this->reference_no,
                'warehouse_id' => $this->warehouse_id,
                'type' => $data['type'] ?? 'd', // default to delivery
                'date' => $data['date'] ?? now()->format('Y-m-d'),
                'estimated_shipment_date' => $data['estimated_shipment_date'] ?? $this->expected_delivery_date,
                'buyer_id' => $this->buyer_id,
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'remarks' => $data['remarks'] ?? $this->remark,
                'sub_total' => $this->sub_total,
                'tax_total' => $this->tax_total,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'grand_total' => $this->grand_total,
                'user_id' => $data['user_id'] ?? $this->user_id ?? auth()->id(),
            ]);

            // Copy quotation items to sales order items
            foreach ($this->items as $quotationItem) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $quotationItem->product_id,
                    'sku' => $quotationItem->sku,
                    'unit_id' => $quotationItem->unit_id,
                    'description' => $quotationItem->title ?? $quotationItem->remark,
                    'rate' => $quotationItem->rate,
                    'original_rate' => $quotationItem->rate,
                    'quantity' => $quotationItem->quantity,
                    'amount' => $quotationItem->amount,
                    'remarks' => $quotationItem->remark,
                ]);
            }

            // Update quotation status to converted
            \App\Services\QuotationService::setStatus($this, 'converted', now()->format(config('project.date_format')), 'Converted to Sales Order');
        });

        return $salesOrder;
    }


}
