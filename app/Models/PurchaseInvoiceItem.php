<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasAdvancedFilter, HasFactory,Searchable;

    public $table = 'purchase_invoice_items';

    protected $orderable = [
        'id',
        'purchase_order.invoice_number',
        'product.code',
        'sku',
        'description',
        'unit.name',
        'rate',
        'quantity',
        'amount',
        'purchase_invoice.invoice_number',
    ];

    protected $filterable = [
        'id',
        'purchase_order.invoice_number',
        'product.code',
        'sku',
        'description',
        'unit.name',
        'rate',
        'quantity',
        'amount',
        'purchase_invoice.invoice_number',
    ];

    protected $fillable = [
        'product_id',
        'sku',
        'description',
        'unit_id',
        'rate',
        'quantity',
        'amount',
        'purchase_invoice_id',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }
}
