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

use App\Attributes\DateAttribute;
use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use App\Traits\Searchable;
use App\Traits\SerializeDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Inward extends Model
{
    use HasAdvancedFilter, Searchable, HasFactory, SerializeDate, DateAttribute, CompanyScopeTrait;

    protected $fillable = [
        'invoice_number',
        'reference_no',
        'date',
        'remark',
        'currency_rate',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'company_id',
        'state_id',
        'supplier_id',
        'warehouse_id',
        'user_id',
    ];

    protected $orderable = [
        'id',
        'invoice_number',
        'reference_no',
        'date',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
    ];

    protected $filterable = [
        'id',
        'invoice_number',
        'reference_no',
        'date',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'company.name',
        'supplier.name',
        'warehouse.name',
        'user.name',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InwardItem::class);
    }

    public function taxes(): MorphMany
    {
        return $this->morphMany(OrderTaxDetail::class, 'taxable')->orderBy('priority');
    }

}
