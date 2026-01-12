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
 *  *  Last modified: 15/01/25, 1:46 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use App\Services\CompanyService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'shelves';

    protected $appends = [
        'active_label',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'name',
        'warehouse_id',
        'warehouse.name',
    ];

    protected $orderable = [
        'id',
        'name',
        'active',
        'warehouse.name',
    ];

    protected $fillable = [
        'name',
        'active',
        'is_default',
        'warehouse_id',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function scopeCompany($q): void
    {
        $warehouseIds = CompanyService::getAccessibleWarehouseIds();
        if (!empty($warehouseIds)) {
            $q->whereIn('warehouse_id', $warehouseIds);
            return;
        }

        $company = CompanyService::getCompanyById();
        if (!$company) {
            return;
        }

        $identifiers = array_values(array_filter([$company->code, $company->name]));
        if (empty($identifiers)) {
            return;
        }

        $q->where(function ($query) use ($identifiers) {
            $first = true;
            foreach ($identifiers as $identifier) {
                if ($first) {
                    $query->where('name', 'like', '%' . $identifier . '%');
                    $first = false;
                } else {
                    $query->orWhere('name', 'like', '%' . $identifier . '%');
                }
            }
        });
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getActiveLabelAttribute()
    {
        return $this->active ? "Yes" : "No";
    }

    public function productStockShelf()
    {
        return $this->hasMany(ProductStockShelf::class, 'shelf_id', 'id');
    }
}
