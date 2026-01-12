<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 6:14 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Subscription;

class ContractRevision extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'contract_revisions';

    protected $appends = [
        'contract_type_label',
    ];

    protected $casts = [
        'limited_installment' => 'boolean',
        'active'              => 'boolean',
    
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    

    public const CONTRACT_TYPE_SELECT = [
        [
            'label' => 'Manual',
            'value' => 'default',
        ],
        [
            'label' => 'Stripe Managed',
            'value' => 'stripe',
        ],
    ];

    protected $filterable = [
        'id',
        'contract_type',
        'start_date',
        'end_date',
        'installment_count',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'contract.date',
        'user.name',
        'stripe_product',
        'stripe_product_price',
        'stripe_subscription_meta',
        'stripe_subscription',
    ];

    protected $orderable = [
        'id',
        'contract_type',
        'start_date',
        'end_date',
        'installment_count',
        'limited_installment',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'contract.date',
        'active',
        'user.name',
        'stripe_product',
        'stripe_product_price',
        'stripe_subscription_meta',
        'stripe_subscription',
    ];

    protected $fillable = [
        'contract_type',
        'start_date',
        'end_date',
        'installment_count',
        'limited_installment',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'contract_id',
        'active',
        'user_id',
        'stripe_product',
        'stripe_product_price',
        'stripe_subscription_meta',
        'stripe_subscription',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getContractTypeLabelAttribute()
    {
        return collect(static::CONTRACT_TYPE_SELECT)->firstWhere('value', $this->contract_type)['label'] ?? '';
    }

    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'stripe_id', 'stripe_subscription');
    }

    public function items()
    {
        return $this->hasMany(ContractItem::class);
    }

    public function getContractTypeAttribute()
    {
        $type = collect(static::CONTRACT_TYPE_SELECT)->firstWhere('value', $this->attributes['contract_type']);
        return $type ?: [];
    }
}
