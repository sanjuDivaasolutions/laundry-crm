<?php

namespace App\Http\Resources\Admin;

use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierEditResource extends JsonResource
{
    public function toArray($request)
    {
        $record = parent::toArray($request);

        if ($this->type) {
            $obj = collect(Supplier::TYPE_SELECT)->firstWhere('value', $this->type);
            if ($obj) {
                $record['type'] = [
                    'value' => $obj['value'],
                    'label' => $obj['label'],
                ];
            }
        }

        return $record;
    }
}
