<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount, 2),
            'payment_method' => $this->payment_method, // Enum
            'payment_method_label' => $this->payment_method?->getLabel() ?? $this->payment_method?->value ?? $this->payment_method,
            'transaction_reference' => $this->transaction_reference,
            'notes' => $this->notes,
            'received_by' => $this->whenLoaded('receivedBy', fn () => $this->receivedBy?->name),
        ];
    }
}
