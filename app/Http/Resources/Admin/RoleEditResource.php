<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleEditResource extends JsonResource
{
    public function toArray($request)
    {
        $permissions = [];
        if($this->whenLoaded('permissions')) {
            $permissions = $this->permissions->pluck('id')->toArray();
        }
        //return parent::toArray($request);
        return [
            'id'            =>  $this->id,
            'title'         =>  $this->whenNotNull($this->title),
            'permissions'   =>  $permissions,
        ];
    }
}
