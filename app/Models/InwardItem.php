<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 05/02/25, 8:03 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InwardItem extends Model
{
    use HasAdvancedFilter, Searchable;

    protected $fillable = [
        'sku',
        'description',
        'rate',
        'quantity',
        'amount',
        'inward_id',
        'product_id',
        'unit_id',
    ];

    protected array $searchable = [
        'sku',
        'description',
    ];

    protected array $filterable = [
        'id',
        'sku',
        'description',
        'rate',
        'quantity',
        'amount',
        'inward.date',
    ];

    protected array $orderable = [
        'id',
        'sku',
        'description',
        'rate',
        'quantity',
        'amount',
    ];

    public function inward(): BelongsTo
    {
        return $this->belongsTo(Inward::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventory(): MorphOne
    {
        return $this->morphOne(ProductInventory::class, 'inventoryable');
    }

    public function inwardItemShelf(): HasMany
    {
        return $this->hasMany(InwardItemShelf::class);
    }

    public function firstInwardItemShelf(): HasOne
    {
        return $this->hasOne(InwardItemShelf::class);
    }
}
