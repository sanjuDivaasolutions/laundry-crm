<?php

namespace App\Http\Resources\Admin;

use App\Models\SalesOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderEditResource extends JsonResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        

        return $result;
    }
}
