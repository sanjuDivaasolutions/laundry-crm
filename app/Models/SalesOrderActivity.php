<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderActivity extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'sales_order_activities';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'sale_order.so_number',
        'title',
        'description',
        'user.name',
    ];

    protected $orderable = [
        'id',
        'sale_order.so_number',
        'title',
        'is_active',
        'description',
        'user.name',
    ];

    protected $fillable = [
        'sale_order_id',
        'title',
        'is_active',
        'description',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
