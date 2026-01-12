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
 *  *  Last modified: 23/01/25, 6:18 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Attributes\DateAttribute;
use App\Services\AuthService;
use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasAdvancedFilter, HasFactory, DateAttribute;

    public $table = 'packages';

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $orderable = [
        'id',
        'sales_order.so_number',
        'sales_invoice.invoice_number',
        'code',
        'reference_no',
        'date',
        'remarks',
        'user.name',
        'packing_type.name',
    ];

    protected $filterable = [
        'id',
        'sales_order.so_number',
        'sales_invoice.invoice_number',
        'code',
        'reference_no',
        'date',
        'remarks',
        'user.name',
        'packing_type.name',
    ];

    protected $fillable = [
        'sales_order_id',
        'sales_invoice_id',
        'code',
        'reference_no',
        'date',
        'remarks',
        'user_id',
        'packing_type_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeCompany($q): void
    {
        $companyId = AuthService::getCompanyId();
        if ($companyId) {
            $q->whereHas('salesInvoice', static function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packingType()
    {
        return $this->belongsTo(PackingType::class);
    }

    public function items()
    {
        return $this->hasMany(PackageItem::class);
    }
}
