<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'estimate_items';

    protected $orderable = [
        'id',
        'estimate.quo_number',
        'product.code',
        'sku',
        'unit.name',
        'description',
        'rate',
        'original_rate',
        'quantity',
        'amount',
        'remarks',
    ];

    protected $filterable = [
        'id',
        'estimate.quo_number',
        'product.code',
        'sku',
        'unit.name',
        'description',
        'rate',
        'original_rate',
        'quantity',
        'amount',
        'remarks',
    ];

    protected $fillable = [
        'estimate_id',
        'product_id',
        'sku',
        'unit_id',
        'description',
        'rate',
        'original_rate',
        'quantity',
        'amount',
        'remarks',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
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
