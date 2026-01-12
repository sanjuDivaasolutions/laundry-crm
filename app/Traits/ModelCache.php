<?php

namespace App\Traits;

use App\Services\ModelCacheService;

trait ModelCache
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootModelCache()
    {
        static::created(function ($model) {
            $model->clearCache();
        });

        static::updated(function ($model) {
            $model->clearCache();
        });
    }

    /**
     * Clear the cache using the specified key.
     *
     * @return void
     */
    public function clearCache()
    {
        if (! $this->cacheKey && ! $this->table) {
            return;
        }
        $cacheKey = $this->cacheKey;
        if (! $cacheKey) {
            $cacheKey = $this->table;
        }
        ModelCacheService::forget($cacheKey);
    }
}
