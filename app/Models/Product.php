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
 *  *  Last modified: 07/01/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable, CompanyScopeTrait;

    public $table = 'products';

    public const TYPE_SELECT = [
        [
            'label' => 'Product',
            'value' => 'product',
        ],
        [
            'label' => 'Service',
            'value' => 'service',
        ],
    ];

    protected $casts = [
        'active'        => 'boolean',
        'is_returnable' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'code',
        'type',
        'name',
        'sku',
        'barcode',
        'category.name',
        'company.name',
        'description',
        'supplier.name',
        'user.name',
        'manufacturer',
        'unit_01.name',
        'unit_02.name',
        'hsn_code',
        'batch_number',
        'warehouse.name',
        'rack.name',
        'stock.shelves.shelf_id',
        'salesInvoiceItem.salesInvoice.date',
        'inwardItem.inward.date',
    ];

    protected $orderable = [
        'id',
        'code',
        'type',
        'name',
        'sku',
        'category.name',
        'company.name',
        'description',
        'supplier.name',
        'active',
        'user.name',
        'manufacturer',
        'unit_01.name',
        'unit_02.name',
        'hsn_code',
        'batch_number',
        'warehouse.name',
        'rack.name',
        'is_returnable',
    ];

    protected $fillable = [
        'code',
        'type',
        'name',
        'sku',
        'barcode',
        'barcode_type',
        'category_id',
        'company_id',
        'description',
        'supplier_id',
        'active',
        'user_id',
        'manufacturer',
        'unit_01_id',
        'unit_02_id',
        'is_returnable',
        'has_inventory',
        'hsn_code',
        'batch_number',
        'warehouse_id',
        'rack_id',
        'created_at',
        'updated_at',
    ];

    public function scopeOnlyProducts($q)
    {
        return $q->where('type', 'product');
    }

    public function scopeOnlyServices($q)
    {
        return $q->where('type', 'service');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function price(): HasOne
    {
        return $this->hasOne(ProductPrice::class);
    }

    public function stock(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function opening(): HasMany
    {
        return $this->hasMany(ProductOpening::class);
    }

    public function unit_01(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function unit_02(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }

    public function getTypeAttribute()
    {
        $type = collect(static::TYPE_SELECT)->firstWhere('value', $this->attributes['type']);
        return $type ?: [];
    }

    public function inwardItem(): HasMany
    {
        return $this->hasMany(InwardItem::class);
    }

    public function contractItem(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function salesInvoiceItem(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function quotationItem(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    // Barcode accessor
    public function getBarcodeImageAttribute()
    {
        return $this->barcode ? route('api.barcodes.image', $this->id) : null;
    }
}
