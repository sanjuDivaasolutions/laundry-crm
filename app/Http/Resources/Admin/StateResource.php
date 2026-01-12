<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'country_id' => $this->country_id,
            'country'    => new CountryResource($this->whenLoaded('country')),
            //'active' => $this->active,
        ];
    }
}
