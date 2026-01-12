<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'state_id' => $this->state_id,
            'state' => new StateResource($this->whenLoaded('state')),
            // 'active' => $this->active,
        ];
    }
}
