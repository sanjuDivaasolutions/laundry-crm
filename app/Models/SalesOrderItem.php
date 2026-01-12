<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'sales_order_items';

    protected $orderable = [
        'id',
        'sales_order.so_number',
        'product.name',
        'sku',
        'unit.name',
        'description',
        'rate',
        'original_rate',
        'quantity',
        'amount',
        'remarks',
        'sku',
    ];

    protected $filterable = [
        'id',
        'sales_order.so_number',
        'product.name',
        'sku',
        'unit.name',
        'description',
        'rate',
        'original_rate',
        'quantity',
        'amount',
        'remarks',
        'sku',
    ];

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'sku',
        'unit_id',
        'description',
        'rate',
        'original_rate',
        'quantity',
        'amount',
        'remarks',
        'sku',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
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
