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

class Service extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'pricing_type',
        'price_per_pound',
        'minimum_weight',
        'display_order',
        'is_active',
    ];

    protected $orderable = [
        'id',
        'name',
        'code',
        'display_order',
        'is_active',
        'created_at',
    ];

    protected $filterable = [
        'id',
        'name',
        'code',
        'is_active',
    ];

    protected $searchable = [
        'id',
        'name',
        'code',
        'description',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'price_per_pound' => 'decimal:2',
        'minimum_weight' => 'decimal:2',
    ];

    public function servicePrices(): HasMany
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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
            ->logOnly(['name', 'code', 'description', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('service')
            ->setDescriptionForEvent(fn (string $eventName): string => "Service {$this->name} was {$eventName}");
    }
}
