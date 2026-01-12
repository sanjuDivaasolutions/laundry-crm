<?php

namespace App\Support;

trait ModelScopes
{
    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
