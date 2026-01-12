<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderEditResource extends JsonResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        return $result;
    }
}
