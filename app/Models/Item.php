<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Item extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'price',
        'display_order',
        'is_active',
    ];

    protected $orderable = [
        'id',
        'name',
        'code',
        'price',
        'display_order',
        'is_active',
        'created_at',
    ];

    protected $filterable = [
        'id',
        'name',
        'code',
        'price',
        'is_active',
    ];

    protected $searchable = [
        'id',
        'name',
        'code',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Order items using this item.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function servicePrices(): HasMany
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('display_order')->orderBy('name');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description', 'price', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('item')
            ->setDescriptionForEvent(fn (string $eventName): string => "Item {$this->name} was {$eventName}");
    }
}
