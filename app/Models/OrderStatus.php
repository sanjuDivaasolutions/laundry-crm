<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class OrderStatus extends Model
{
    /**
     * Get the ID for a given order status name.
     * Results are cached for the lifetime of the request.
     */
    public static function idFor(string $statusName): int
    {
        $map = Cache::store('array')->rememberForever('order_status_map', function () {
            return static::pluck('id', 'status_name')->toArray();
        });

        return $map[$statusName] ?? throw new \RuntimeException("Order status '{$statusName}' not found in database.");
    }

    protected $table = 'order_status';

    public $timestamps = false;

    protected $fillable = [
        'status_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('display_order');
    }
}
