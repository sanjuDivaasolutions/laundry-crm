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

class PurchaseInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'formatted_invoice_number' => $this->formatted_invoice_number ?? 'PI-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'date' => $this->date,
            'due_date' => $this->due_date,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'name' => $this->supplier->name,
                    'code' => $this->supplier->code,
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
            'purchase_order_id' => $this->purchase_order_id,
            'purchaseOrder' => $this->whenLoaded('purchaseOrder', function () {
                return [
                    'id' => $this->purchaseOrder->id,
                    'po_number' => $this->purchaseOrder->po_number,
                ];
            }),
            'type' => $this->type,
            'type_label' => $this->type_label ?? '',
            'reference_no' => $this->reference_no,
            'remark' => $this->remark,
            'sub_total' => $this->sub_total,
            'tax_total' => $this->tax_total,
            'tax_rate' => $this->tax_rate,
            'grand_total' => $this->grand_total,
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}