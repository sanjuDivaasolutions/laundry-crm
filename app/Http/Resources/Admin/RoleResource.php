<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id'    =>  $this->id,
            'title' =>  $this->whenNotNull($this->title),
            'permissions' =>  PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
