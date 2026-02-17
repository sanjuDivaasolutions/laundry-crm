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

class Customer extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'name',
        'phone',
        'address',
        'is_active',
        'loyalty_points',
        'total_orders_count',
        'total_spent',
        'loyalty_tier',
    ];

    protected $orderable = [
        'id',
        'customer_code',
        'name',
        'phone',
        'is_active',
    ];

    protected $filterable = [
        'id',
        'customer_code',
        'name',
        'phone',
        'address',
        'is_active',
    ];

    protected $searchable = [
        'id',
        'customer_code',
        'name',
        'phone',
        'address',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'loyalty_points' => 'integer',
        'total_orders_count' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'address', 'is_active', 'loyalty_points', 'loyalty_tier'])
            ->logOnlyDirty()
            ->useLogName('customer')
            ->setDescriptionForEvent(fn (string $eventName): string => "Customer {$this->name} was {$eventName}");
    }
}
