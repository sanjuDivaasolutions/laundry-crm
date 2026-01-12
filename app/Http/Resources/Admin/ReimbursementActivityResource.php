<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ReimbursementActivityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'expenseReimbursement' => $this->whenLoaded('expenseReimbursement', $this->expenseReimbursement ? $this->expenseReimbursement->date : ''),
            'user' => $this->whenLoaded('user', $this->user ? $this->user->name : ''),
            'date' => $this->date,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'remark' => $this->remark,
        ];
    }
}
