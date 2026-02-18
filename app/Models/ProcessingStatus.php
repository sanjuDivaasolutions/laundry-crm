<?php

namespace App\Models;

use App\Enums\ProcessingStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProcessingStatus extends Model
{
    /**
     * Get the ID for a given processing status enum value.
     * Results are cached for the lifetime of the request.
     */
    public static function idFor(ProcessingStatusEnum $status): int
    {
        $map = Cache::store('array')->rememberForever('processing_status_map', function () {
            return static::pluck('id', 'status_name')->toArray();
        });

        return $map[$status->value] ?? throw new \RuntimeException("Processing status '{$status->value}' not found in database.");
    }

    protected $table = 'processing_status';

    public $timestamps = false; // Using custom seeders, no timestamps in migration for this lookup

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
