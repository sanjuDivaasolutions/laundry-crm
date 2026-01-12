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
 *  *  Last modified: 06/01/25, 6:30 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'product_inventories';

    protected $appends = [
        'reason_label',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $orderable = [
        'id',
        'product.code',
        'warehouse.name',
        'batch.name',
        'shelf.name',
        'reason',
        'date',
        'rate',
        'quantity',
        'amount',
        'user.name',
        'unit.name',
    ];

    protected $filterable = [
        'id',
        'product.code',
        'warehouse.name',
        'batch.name',
        'shelf.name',
        'reason',
        'date',
        'rate',
        'quantity',
        'amount',
        'user.name',
        'unit.name',
    ];

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'shelf_id',
        'reason',
        'date',
        'rate',
        'quantity',
        'amount',
        'user_id',
        'unit_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const REASON_SELECT = [
        [
            'label' => 'Opening Stock',
            'value' => 'opening',
        ],
        [
            'label' => 'Purchase',
            'value' => 'purchase',
        ],
        [
            'label' => 'Sale',
            'value' => 'sales',
        ],
        [
            'label' => 'Purchase Return',
            'value' => 'purchaseReturn',
        ],
        [
            'label' => 'Sale Return',
            'value' => 'saleReturn',
        ],
        [
            'label' => 'transfer In',
            'value' => 'transferIn',
        ],
        [
            'label' => 'transfer Out',
            'value' => 'transferOut',
        ],
        // Source: move
        [
            'label' => 'Transfer Out',
            'value' => 'Source: move',
        ],
        [
            'label' => 'Transfer In',
            'value' => 'Target: move',
        ],
        [
            'label' => 'Surplus',
            'value' => 'surplus',
        ],
        [
            'label' => 'Damaged',
            'value' => 'damaged',
        ],
        [
            'label' => 'Other',
            'value' => 'other',
        ],
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function inventoryable()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function getReasonLabelAttribute()
    {
        return collect(static::REASON_SELECT)->firstWhere('value', $this->reason)['label'] ?? '';
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }


    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
