<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'purchase_order_items';

    protected $orderable = [
        'id',
        'purchase_order.po_number',
        'product.code',
        'sku',
        'description',
        'unit.name',
        'rate',
        'quantity',
        'amount',
    ];

    protected $filterable = [
        'id',
        'purchase_order.po_number',
        'product.code',
        'sku',
        'description',
        'unit.name',
        'rate',
        'quantity',
        'amount',
    ];

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'sku',
        'description',
        'unit_id',
        'rate',
        'quantity',
        'amount',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
