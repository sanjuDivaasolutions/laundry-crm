<?php

namespace App\Traits;

use App\Models\Product;
use App\Services\QueryService;

trait ProjectSearch
{
    public function inventoryTypes($q) {
        return QueryService::searchArray(Product::INVENTORY_TYPE_SELECT,$q,$this->idValue,$this->labelValue);
    }

    public function itemTypes($q) {
        return QueryService::searchArray(Product::ITEM_TYPE_SELECT,$q,$this->idValue,$this->labelValue);
    }
}
