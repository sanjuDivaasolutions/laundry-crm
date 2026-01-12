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
 *  *  Last modified: 07/01/25, 5:11 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use App\Traits\Searchable;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable, CompanyScopeTrait;

    public $table = 'sales_orders';

    protected $appends = [
        'type_label',
    ];

    protected $casts = [
        'date' => 'datetime',
        'estimated_shipment_date' => 'datetime',
    ];

    public const TYPE_SELECT = [
        [
            'label' => 'Pickup',
            'value' => 'p',
        ],
        [
            'label' => 'Delivery',
            'value' => 'd',
        ],
    ];

    protected $orderable = [
        'id',
        'so_number',
        'quotation_no',
        'reference_no',
        'warehouse.name',
        'type',
        'date',
        'estimated_shipment_date',
        'buyer.name',
        'payment_term.name',
        'remarks',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'user.name',
    ];

    protected $filterable = [
        'id',
        'so_number',
        'quotation_no',
        'reference_no',
        'warehouse.name',
        'type',
        'date',
        'estimated_shipment_date',
        'buyer.name',
        'payment_term.name',
        'remarks',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'user.name',
    ];

    protected $fillable = [
        'company_id',
        'so_number',
        'quotation_no',
        'reference_no',
        'warehouse_id',
        'type',
        'date',
        'estimated_shipment_date',
        'buyer_id',
        'payment_term_id',
        'remarks',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getTypeLabelAttribute()
    {
        return collect(static::TYPE_SELECT)->firstWhere('value', $this->type)['label'] ?? '';
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getEstimatedShipmentDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setEstimatedShipmentDateAttribute($value)
    {
        $this->attributes['estimated_shipment_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function taxes(): MorphMany
    {
        return $this->morphMany(OrderTaxDetail::class, 'taxable')->orderBy('priority');
    }

}
