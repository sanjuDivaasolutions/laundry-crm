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
 *  *  Last modified: 22/01/25, 5:14 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'sales_invoice_items';

    protected $orderable = [
        'id',
        'sales_invoice.invoice_number',
        'product.code',
        'description',
        'sku',
        'unit.name',
        'remark',
        'quantity',
        'rate',
        'original_rate',
        'amount',
    ];

    protected $filterable = [
        'id',
        'sales_invoice.invoice_number',
        'sales_invoice.date',
        'product.code',
        'description',
        'sku',
        'unit.name',
        'remark',
        'quantity',
        'rate',
        'original_rate',
        'amount',
    ];

    protected $fillable = [
        'sales_invoice_id',
        'product_id',
        'description',
        'sku',
        'unit_id',
        'shelf_id',
        'remark',
        'quantity',
        'rate',
        'original_rate',
        'amount',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function inventory()
    {
        return $this->morphOne(ProductInventory::class, 'inventoryable');
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }
}
