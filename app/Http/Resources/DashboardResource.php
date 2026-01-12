<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
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
            'today_sales' => $this->resource['today_sales'],
            'today_purchase' => $this->resource['today_purchase'],
            'today_sales_invoices' => $this->resource['today_sales_invoices'],
            'today_purchase_invoices' => $this->resource['today_purchase_invoices'],
            'low_stock_items' => $this->resource['low_stock_items'],
            'pending_quotations' => $this->resource['pending_quotations'],
        ];
    }
}
