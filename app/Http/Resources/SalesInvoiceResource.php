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
 *  *  Last modified: 07/01/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'formatted_invoice_number' => 'SI-'.str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'date' => $this->date,
            'due_date' => $this->due_date,
            'buyer_id' => $this->buyer_id,
            'buyer' => $this->whenLoaded('buyer', function () {
                return [
                    'id' => $this->buyer->id,
                    'name' => $this->buyer->name,
                    'code' => $this->buyer->code,
                ];
            }),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                    'code' => $this->warehouse->code,
                    'full_name' => $this->warehouse->full_name ?? "{$this->warehouse->code} - {$this->warehouse->name}",
                ];
            }),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'agent_id' => $this->agent_id,
            'agent' => $this->whenLoaded('agent', function () {
                return [
                    'id' => $this->agent->id,
                    'name' => $this->agent->name,
                    'code' => $this->agent->code,
                ];
            }),
            'sales_order_id' => $this->sales_order_id,
            'salesOrder' => $this->whenLoaded('salesOrder', function () {
                return [
                    'id' => $this->salesOrder->id,
                    'so_number' => $this->salesOrder->so_number,
                ];
            }),
            'payment_term_id' => $this->payment_term_id,
            'paymentTerm' => $this->whenLoaded('paymentTerm', function () {
                return [
                    'id' => $this->paymentTerm->id,
                    'name' => $this->paymentTerm->name,
                    'days' => $this->paymentTerm->days,
                ];
            }),
            'state_id' => $this->state_id,
            'state' => $this->whenLoaded('state', function () {
                return [
                    'id' => $this->state->id,
                    'name' => $this->state->name,
                ];
            }),
            'type' => $this->type,
            'type_label' => $this->type_label ?? '',
            'order_type' => $this->order_type,
            'order_type_label' => $this->when($this->order_type, function () {
                $types = [
                    'product' => 'Product',
                    'service' => 'Service',
                    'contract' => 'Contract',
                ];

                return $types[$this->order_type] ?? $this->order_type;
            }),
            'reference_no' => $this->reference_no,
            'remark' => $this->remark,
            'sub_total' => $this->sub_total,
            'tax_total' => $this->tax_total,
            'tax_rate' => $this->tax_rate,
            'grand_total' => $this->grand_total,
            'commission' => $this->commission,
            'commission_total' => $this->commission_total,
            'is_taxable' => $this->is_taxable,
            'is_taxable_label' => $this->is_taxable_label ?? ($this->is_taxable ? 'Yes' : 'No'),
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->payment_status_label ?? 'Unknown',
            'payment_status_badge' => $this->payment_status_badge ?? '<span class="badge badge-secondary">Unknown</span>',
            'total_paid' => $this->total_paid,
            'pending_amount' => $this->pending_amount,
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'code' => $item->product->code,
                            'sku' => $item->product->sku,
                            'hsn_code' => $item->product->hsn_code,
                            'batch_number' => $item->product->batch_number,
                            'type' => $item->product->type,
                            'unit_01' => $item->product->unit_01 ? [
                                'id' => $item->product->unit_01->id,
                                'name' => $item->product->unit_01->name,
                            ] : null,
                            'unit_02' => $item->product->unit_02 ? [
                                'id' => $item->product->unit_02->id,
                                'name' => $item->product->unit_02->name,
                            ] : null,
                        ] : null,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'tax_rate' => $item->tax_rate,
                        'total' => $item->total,
                    ];
                });
            }),
            'taxes' => $this->whenLoaded('taxes'),
            'payments' => $this->whenLoaded('payments'),
            'package' => $this->whenLoaded('package'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
