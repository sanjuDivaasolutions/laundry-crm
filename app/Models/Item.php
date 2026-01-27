<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Item $item) {
            if (empty($item->code)) {
                $item->code = static::generateCode($item->tenant_id);
            }
        });
    }

    /**
     * Generate unique item code for tenant.
     */
    public static function generateCode(?int $tenantId = null): string
    {
        $prefix = 'ITM';
        $tenantId = $tenantId ?? app(\App\Services\TenantService::class)->getId();

        $lastItem = static::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('code', 'like', $prefix.'-%')
            ->orderByRaw('CAST(SUBSTRING(code, 5) AS UNSIGNED) DESC')
            ->first();

        if ($lastItem && preg_match('/^'.$prefix.'-(\d+)$/', $lastItem->code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%04d', $prefix, $nextNumber);
    }

    protected $fillable = [
        'tenant_id',
        'category_id',
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
        'category_id',
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
     * Primary category for this item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Order items using this item.
     */
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
}
