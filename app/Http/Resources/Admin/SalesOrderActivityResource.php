<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderActivityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'salesOrder' => $this->whenLoaded('salesOrder', $this->salesOrder ? $this->salesOrder->so_number : ''),
            'user' => $this->whenLoaded('user', $this->user ? $this->user->name : ''),
            'date' => $this->date,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'remark' => $this->remark,
        ];
    }
}
