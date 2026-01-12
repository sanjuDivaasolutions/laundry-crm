<?php

namespace App\Http\Resources\Admin;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductEditResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);

        /*if($this->inventory_type) {
            $inventory_type = collect(Product::INVENTORY_TYPE_SELECT)->firstWhere('value', $this->inventory_type);
            $result['inventory_type'] = [
                'value' => $inventory_type['value'],
                'label' => $inventory_type['label'],
            ];
        }

        if($this->item_type) {
            $item_type = collect(Product::ITEM_TYPE_SELECT)->firstWhere('value', $this->item_type);
            $result['item_type'] = [
                'value' => $item_type['value'],
                'label' => $item_type['label'],
            ];
        }

        return $result;*/
    }
}
