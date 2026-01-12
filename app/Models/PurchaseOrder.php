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
 *  *  Last modified: 16/10/24, 5:20 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'purchase_orders';

    protected $appends = [
        'discount_type_label',
    ];

    protected $casts = [
        'date' => 'datetime',
        'estimated_shipment_date' => 'datetime',
    ];


    public const DISCOUNT_TYPE_SELECT = [
        [
            'label' => 'Percent',
            'value' => 'p',
        ],
        [
            'label' => 'Fixed',
            'value' => 'f',
        ],
    ];

    protected $orderable = [
        'id',
        'po_number',
        'date',
        'estimated_shipment_date',
        'supplier.display_name',
        'payment_term.name',
        'shipment_mode.name',
        'warehouse.name',
        'remarks',
        'user.name',
        'freight_total',
        'discount_type',
        'discount_total',
        'discount_rate',
        'sub_total',
        'tax_rate',
        'tax_total',
        'grand_total',
    ];

    protected $filterable = [
        'id',
        'po_number',
        'date',
        'estimated_shipment_date',
        'supplier.display_name',
        'payment_term.name',
        'shipment_mode.name',
        'warehouse.name',
        'remarks',
        'user.name',
        'freight_total',
        'discount_type',
        'discount_total',
        'discount_rate',
        'sub_total',
        'tax_rate',
        'tax_total',
        'grand_total',
    ];

    protected $fillable = [
        'company_id',
        'po_number',
        'date',
        'estimated_shipment_date',
        'supplier_id',
        'payment_term_id',
        'shipment_mode_id',
        'warehouse_id',
        'remarks',
        'user_id',
        'freight_total',
        'discount_type',
        'discount_total',
        'discount_rate',
        'sub_total',
        'tax_rate',
        'tax_total',
        'grand_total',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
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


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function shipmentMode()
    {
        return $this->belongsTo(ShipmentMode::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDiscountTypeLabelAttribute()
    {
        return collect(static::DISCOUNT_TYPE_SELECT)->firstWhere('value', $this->discount_type)['label'] ?? '';
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
