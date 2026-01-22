<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'name',
        'phone',
        'address',
        'is_active',
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
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
