<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Order;
use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    protected $fillable = [
        'order_id',
        'category_id',
        'item_name',
        'service_name',
        'quantity',
        'unit_price',
        'total_price',
        'barcode',
        'notes',
    ];

    protected $orderable = [
        'id',
        'item_name',
        'service_name',
        'quantity',
        'total_price',
    ];

    protected $filterable = [
        'id',
        'order_id',
        'category_id',
        'item_name',
        'service_name',
        'barcode',
    ];

    protected $searchable = [
        'id',
        'item_name',
        'service_name',
        'barcode',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
