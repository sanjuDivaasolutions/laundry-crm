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
 *  *  Last modified: 05/02/25, 6:51 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Attributes\DateAttribute;
use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use App\Services\AuthService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InventoryAdjustment extends Model
{
    use HasAdvancedFilter, DateAttribute, Searchable;

    protected array $overrideOrderFields = [
        'reason_label' => 'reason',
    ];

    protected array $filterable = [
        'code',
        'date',
        'reason',
        'remark',
        'adjusted_quantity',
    ];

    protected array $orderable = [
        'id',
        'code',
        'date',
        'reason',
        'remark',
        'adjusted_quantity',
        'product.name',
        'shelf.name',
        'target_shelf.name',
        'user.name',
    ];

    protected $fillable = [
        'code',
        'date',
        'reason',
        'remark',
        'product_id',
        'shelf_id',
        'target_shelf_id',
        'adjusted_quantity',
        'user_id',
    ];

    public const REASON_SELECT = [
        [
            'label' => 'Damaged',
            'value' => 'damaged',
        ],
        [
            'label' => 'Expired',
            'value' => 'expired',
        ],
        [
            'label' => 'Surplus',
            'value' => 'surplus',
        ],
        [
            'label' => 'Other',
            'value' => 'other',
        ],
        [
            'label' => 'Move to another shelf',
            'value' => 'move',
        ],
    ];

    public function scopeCompany($q): void
    {
        $companyId = AuthService::getCompanyId();
        if (!$companyId) {
            return;
        }

        $q->whereHas('product', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function targetShelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'target_shelf_id');
    }

    public function inventory(): MorphOne
    {
        return $this->morphOne(ProductInventory::class, 'inventoryable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
