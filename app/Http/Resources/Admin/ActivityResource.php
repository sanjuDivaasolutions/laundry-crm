<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type ? class_basename($this->subject_type) : null,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type ? class_basename($this->causer_type) : null,
            'causer_id' => $this->causer_id,
            'causer_name' => $this->causer?->name,
            'properties' => $this->properties,
            'event' => $this->event,
            'batch_uuid' => $this->batch_uuid,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
