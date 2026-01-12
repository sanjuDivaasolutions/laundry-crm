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
 *  *  Last modified: 18/11/24, 6:29 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Models;

use App\Models\Supplier;
use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable, CompanyScopeTrait;

    public $table = 'buyers';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'code',
        'display_name',
        'name',
        'currency.code',
        'paymentTerm.name',
        'billingAddress.name',
        'shippingAddress.name',
        'remarks',
        'phone',
        'email',
        'agent_name',
        'commission_rate',
    ];

    protected $orderable = [
        'id',
        'code',
        'display_name',
        'name',
        'active',
        'currency.code',
        'paymentTerm.name',
        'billingAddress.name',
        'shippingAddress.name',
        'remarks',
        'phone',
        'email',
        'agent_name',
        'commission_rate',
    ];

    protected $fillable = [
        'code',
        'display_name',
        'name',
        'active',
        'company_id',
        'currency_id',
        'payment_term_id',
        'agent_id',
        'billing_address_id',
        'shipping_address_id',
        'remarks',
        'phone',
        'email',
        'agent_name',
        'commission_rate',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(ContactAddress::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ContactAddress::class);
    }

    public function agent()
    {
        return $this->belongsTo(Supplier::class, 'agent_id');
    }

}
