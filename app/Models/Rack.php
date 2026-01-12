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
 *  *  Last modified: 07/01/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Services\CompanyService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rack extends Model
{
    use HasAdvancedFilter, HasFactory, SoftDeletes;

    public $table = 'racks';

    protected $orderable = [
        'id',
        'name',
        'code',
        'warehouse.name',
        'description',
        'capacity',
        'active',
        'created_at',
        'updated_at',
    ];

    protected $filterable = [
        'id',
        'name',
        'code',
        'warehouse.name',
        'description',
        'capacity',
        'active',
    ];

    protected $fillable = [
        'name',
        'code',
        'warehouse_id',
        'description',
        'capacity',
        'length',
        'width',
        'height',
        'weight_capacity',
        'active',
        'company_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'capacity' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'weight_capacity' => 'decimal:2',
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

        $table = $this->getTable();
        $code = $company->code;
        $name = $company->name;

        $q->where(function ($query) use ($table, $code, $name) {
            $hasCondition = false;

            if ($code) {
                $query->where(function ($nested) use ($table, $code) {
                    $nested->where($table . '.name', 'like', '%' . $code . '%')
                        ->orWhere($table . '.code', 'like', '%' . $code . '%');
                });
                $hasCondition = true;
            }

            if ($name) {
                if ($hasCondition) {
                    $query->orWhere($table . '.name', 'like', '%' . $name . '%');
                } else {
                    $query->where($table . '.name', 'like', '%' . $name . '%');
                }
            }
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getFullNameAttribute()
    {
        return "{$this->code} - {$this->name}";
    }

    public function getFullLocationAttribute()
    {
        $warehouseName = $this->warehouse ? $this->warehouse->name : 'Unknown Warehouse';
        return "{$warehouseName} > {$this->name}";
    }

    public function getVolumeAttribute()
    {
        return $this->length * $this->width * $this->height;
    }
}