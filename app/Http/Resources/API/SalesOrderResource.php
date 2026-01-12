<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'buyer_id' => $this->buyer_id,
            'buyer' => $this->when($this->relationLoaded('buyer'), function () {
                return [
                    'id' => $this->buyer->id,
                    'code' => $this->buyer->code,
                    'display_name' => $this->buyer->display_name,
                    'name' => $this->buyer->name,
                ];
            }),
            'agent_id' => $this->agent_id,
            'agent' => $this->when($this->relationLoaded('agent'), function () {
                return $this->agent ? [
                    'id' => $this->agent->id,
                    'code' => $this->agent->code,
                    'name' => $this->agent->name,
                    'email' => $this->agent->email,
                ] : null;
            }),
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'order_type' => $this->order_type,
            'date' => $this->date,
            'due_date' => $this->due_date,
            'sub_total' => (float) $this->sub_total,
            'tax_total' => (float) $this->tax_total,
            'grand_total' => (float) $this->grand_total,
            'tax_rate' => (float) $this->tax_rate,
            'is_taxable' => $this->is_taxable,
            'status' => $this->status,
            'notes' => $this->notes,
            'commission' => (float) ($this->commission ?? 0),
            'commission_total' => (float) ($this->commission_total ?? 0),
            'items' => $this->when($this->relationLoaded('items'), function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product' => $this->when($item->relationLoaded('product'), function () use ($item) {
                            return $item->product ? [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'code' => $item->product->code,
                                'sku' => $item->product->sku,
                            ] : null;
                        }),
                        'description' => $item->description,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'tax_rate' => (float) $item->tax_rate,
                        'tax_total' => (float) $item->tax_total,
                        'sub_total' => (float) $item->sub_total,
                        'grand_total' => (float) $item->grand_total,
                    ];
                });
            }),
            'commissions' => $this->when($this->relationLoaded('commissions'), function () {
                return $this->commissions->map(function ($commission) {
                    return [
                        'id' => $commission->id,
                        'commission_amount' => (float) $commission->commission_amount,
                        'commission_rate' => (float) $commission->commission_rate,
                        'commission_type' => $commission->commission_type,
                        'status' => $commission->status,
                        'commission_date' => $commission->commission_date,
                        'paid_date' => $commission->paid_date,
                        'notes' => $commission->notes,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
