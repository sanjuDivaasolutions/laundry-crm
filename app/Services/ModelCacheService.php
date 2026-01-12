<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ModelCacheService
{
    public static function has($key)
    {
        if (! config('system.model.cache.enabled')) {
            return false;
        }
        $prefix = config('system.model.cache.prefix');

        return Cache::has("$prefix-$key");
    }

    public static function get($key, $default = null)
    {
        $prefix = config('system.model.cache.prefix');

        return Cache::get("$prefix-$key", $default);
    }

    public static function set($key, $value, $ttl = null)
    {
        $prefix = config('system.model.cache.prefix');
        if ($ttl) {
            Cache::put("$prefix-$key", $value, $ttl);
        } else {
            Cache::forever("$prefix-$key", $value);
        }
    }

    public static function forget($key)
    {
        $prefix = config('system.model.cache.prefix');
        Cache::forget("$prefix-$key");
    }
}
