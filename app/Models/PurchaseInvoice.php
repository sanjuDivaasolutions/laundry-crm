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
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'purchase_invoices';

    protected $appends = [
        'type_label',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_date' => 'datetime',
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
        'purchase_order.po_number',
        'invoice_number',
        'date',
        'due_date',
        'supplier.code',
        'remark',
        'user.name',
        'type',
        'reference_no',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
    ];

    protected $filterable = [
        'id',
        'purchase_order.po_number',
        'invoice_number',
        'date',
        'due_date',
        'supplier.code',
        'supplier.name',
        'remark',
        'user.name',
        'type',
        'reference_no',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
    ];

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'invoice_number',
        'date',
        'due_date',
        'supplier_id',
        'remark',
        'user_id',
        'type',
        'reference_no',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getDueDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute()
    {
        return collect(static::TYPE_SELECT)->firstWhere('value', $this->type)['label'] ?? '';
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
