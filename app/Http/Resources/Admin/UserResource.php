<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id'           => $this->id,
            'name'         => $this->whenNotNull($this->name),
            'email'        => $this->whenNotNull($this->email),
            'role_titles'  => $this->whenLoaded('roles', $this->roles->pluck('title')->implode(', ')),
            'roles'        => RoleResource::collection($this->whenLoaded('roles')),
            'active'       => $this->active,
            'active_label' => $this->active_label,
        ];
    }
}
