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
 *  *  Last modified: 13/01/25, 9:22 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'product_stocks';

    protected $casts = [
        'modified' => 'datetime',
    ];

    protected $orderable = [
        'id',
        'product.name',
        'warehouse.name',
        'on_hand',
        'in_transit',
        'modified',
    ];

    protected $filterable = [
        'id',
        'product.name',
        'warehouse.name',
        'on_hand',
        'in_transit',
        'modified',
    ];

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'on_hand',
        'in_transit',
        'modified',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function shelves()
    {
        return $this->hasMany(ProductStockShelf::class);
    }

    public function getModifiedAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setModifiedAttribute($value)
    {
        $this->attributes['modified'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }
}
