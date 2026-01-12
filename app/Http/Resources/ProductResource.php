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

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'type_label' => $this->type['label'] ?? $this->type,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'barcode_type' => $this->barcode_type,
            'barcode_image' => $this->barcode_image ?? null,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),
            'description' => $this->description,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'name' => $this->supplier->name,
                    'code' => $this->supplier->code,
                ];
            }),
            'active' => $this->active,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'manufacturer' => $this->manufacturer,
            'unit_01_id' => $this->unit_01_id,
            'unit_01' => $this->whenLoaded('unit_01', function () {
                return [
                    'id' => $this->unit_01->id,
                    'name' => $this->unit_01->name,
                ];
            }),
            'unit_02_id' => $this->unit_02_id,
            'unit_02' => $this->whenLoaded('unit_02', function () {
                return [
                    'id' => $this->unit_02->id,
                    'name' => $this->unit_02->name,
                ];
            }),
            'is_returnable' => $this->is_returnable,
            'has_inventory' => $this->has_inventory,
            'hsn_code' => $this->hsn_code,
            'batch_number' => $this->batch_number,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                    'code' => $this->warehouse->code,
                    'full_name' => $this->warehouse->full_name ?? "{$this->warehouse->code} - {$this->warehouse->name}",
                ];
            }),
            'rack_id' => $this->rack_id,
            'rack' => $this->whenLoaded('rack', function () {
                return [
                    'id' => $this->rack->id,
                    'name' => $this->rack->name,
                    'code' => $this->rack->code,
                    'full_name' => $this->rack->full_name ?? "{$this->rack->code} - {$this->rack->name}",
                    'full_location' => $this->rack->full_location ?? null,
                ];
            }),
            'features' => $this->whenLoaded('features'),
            'prices' => $this->whenLoaded('prices'),
            'price' => $this->whenLoaded('price', function () {
                return [
                    'id' => $this->price->id,
                    'price' => $this->price->price,
                    'currency' => $this->price->currency,
                ];
            }),
            'stock' => $this->whenLoaded('stock'),
            'opening' => $this->whenLoaded('opening'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}