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
 *  *  Last modified: 05/02/25, 7:30 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasAdvancedFilter, HasFactory, CompanyScopeTrait;

    public $table = 'contracts';

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $orderable = [
        'id',
        'code',
        'buyer.name',
        'user.name',
        'date',
        'other_terms',
        'remark',
        'stripe_product',
        'stripe_product_price',
        'stripe_subscription_meta',
    ];

    protected $filterable = [
        'id',
        'code',
        'buyer.name',
        'user.name',
        'date',
        'other_terms',
        'remark',
        'contract_term.name',
        'stripe_product',
        'stripe_product_price',
        'stripe_subscription_meta',
    ];

    protected $fillable = [
        'code',
        'buyer_id',
        'company_id',
        'user_id',
        'date',
        'other_terms',
        'remark',
        'stripe_product',
        'stripe_product_price',
        'stripe_subscription_meta',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function term()
    {
        return $this->belongsToMany(ContractTerm::class);
    }

    public function items()
    {
        return $this->hasMany(ContractItem::class);
    }

    public function revision()
    {
        return $this->hasOne(ContractRevision::class)->where('active', 1);
    }

    public function revisions()
    {
        return $this->hasMany(ContractRevision::class);
    }
}
