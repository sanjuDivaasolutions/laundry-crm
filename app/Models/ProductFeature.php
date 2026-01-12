<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFeature extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'product_features';

    protected $orderable = [
        'id',
        'name',
        'product.name',
        'feature.name',
    ];

    protected $filterable = [
        'id',
        'name',
        'product.name',
        'feature.name',
    ];

    protected $fillable = [
        'name',
        'product_id',
        'feature_id',
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

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
