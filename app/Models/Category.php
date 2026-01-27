<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasAdvancedFilter;

    protected $fillable = [
        'name',
        'display_order',
        'is_active',
    ];

    protected $orderable = [
        'id',
        'name',
        'display_order',
        'is_active',
    ];

    protected $filterable = [
        'id',
        'name',
        'is_active',
    ];

    protected $searchable = [
        'id',
        'name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Items in this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
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
