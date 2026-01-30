<?php

namespace App\Http\Resources\Admin;

use App\Models\OrderStatus;
use App\Models\ProcessingStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $oldStatusName = null;
        $newStatusName = null;

        // Get status names based on type
        if ($this->status_type?->value === 'processing') {
            $oldStatusName = $this->old_status_id ? ProcessingStatus::find($this->old_status_id)?->status_name : null;
            $newStatusName = $this->new_status_id ? ProcessingStatus::find($this->new_status_id)?->status_name : null;
        } elseif ($this->status_type?->value === 'order') {
            $oldStatusName = $this->old_status_id ? OrderStatus::find($this->old_status_id)?->status_name : null;
            $newStatusName = $this->new_status_id ? OrderStatus::find($this->new_status_id)?->status_name : null;
        }

        return [
            'id' => $this->id,
            'status_type' => $this->status_type?->value,
            'status_type_label' => $this->getStatusTypeLabel(),
            'old_status_id' => $this->old_status_id,
            'old_status_name' => $oldStatusName,
            'new_status_id' => $this->new_status_id,
            'new_status_name' => $newStatusName,
            'remarks' => $this->remarks,
            'changed_at' => $this->changed_at?->format('Y-m-d H:i:s'),
            'changed_at_formatted' => $this->changed_at?->format('M d, Y h:i A'),
        ];
    }

    private function getStatusTypeLabel(): string
    {
        return match ($this->status_type?->value) {
            'processing' => 'Processing Status',
            'order' => 'Order Status',
            'payment' => 'Payment',
            default => 'Status',
        };
    }
}
