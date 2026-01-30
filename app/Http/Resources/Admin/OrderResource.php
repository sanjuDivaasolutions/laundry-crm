<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'order_date' => $this->order_date?->format('Y-m-d'),
            'promised_date' => $this->promised_date?->format('Y-m-d'),
            'total_items' => $this->total_items,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'balance_amount' => $this->balance_amount,
            'payment_status' => $this->payment_status,
            'processing_status' => $this->whenLoaded('processingStatus', fn () => $this->processingStatus?->status_name),
            'order_status' => $this->whenLoaded('orderStatus', fn () => $this->orderStatus?->status_name),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'urgent' => $this->urgent,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
