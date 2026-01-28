<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * TenantCacheService
 *
 * Provides tenant-scoped caching to prevent cache pollution between tenants.
 * All cache keys are automatically prefixed with the tenant ID.
 *
 * Design Decisions (from interview):
 * - Prefixed Keys: All cache keys prefixed with tenant:{id}:
 * - Request singleton caching via TenantService
 * - No Redis tags (fallback to key prefixing for compatibility)
 *
 * Usage:
 * $cache = app(TenantCacheService::class);
 * $cache->put('items:list', $items, 3600);
 * $items = $cache->get('items:list');
 * $items = $cache->remember('items:list', 3600, fn() => Item::all());
 */
class TenantCacheService
{
    /**
     * Default cache TTL in seconds (1 hour).
     */
    protected const DEFAULT_TTL = 3600;

    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Get the cache key prefix for current tenant.
     */
    protected function getPrefix(): string
    {
        $tenantId = $this->tenantService->getId();

        if (!$tenantId) {
            return 'global:';
        }

        return "tenant:{$tenantId}:";
    }

    /**
     * Generate tenant-prefixed cache key.
     *
     * @param string $key Original key
     * @return string Prefixed key
     */
    public function key(string $key): string
    {
        return $this->getPrefix() . $key;
    }

    /**
     * Check if a key exists in cache.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Cache::has($this->key($key));
    }

    /**
     * Get a value from cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($this->key($key), $default);
    }

    /**
     * Get multiple values from cache.
     *
     * @param array $keys
     * @return array
     */
    public function many(array $keys): array
    {
        $prefixedKeys = array_map(fn($k) => $this->key($k), $keys);
        $values = Cache::many($prefixedKeys);

        // Re-map to original keys
        $result = [];
        foreach ($keys as $index => $originalKey) {
            $prefixedKey = $prefixedKeys[$index];
            $result[$originalKey] = $values[$prefixedKey] ?? null;
        }

        return $result;
    }

    /**
     * Store a value in cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl TTL in seconds (null for default)
     * @return bool
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? config('tenancy.cache.default_ttl', self::DEFAULT_TTL);

        return Cache::put($this->key($key), $value, $ttl);
    }

    /**
     * Store a value forever.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        return Cache::forever($this->key($key), $value);
    }

    /**
     * Get and delete a value.
     *
     * @param string $key
     * @return mixed
     */
    public function pull(string $key): mixed
    {
        return Cache::pull($this->key($key));
    }

    /**
     * Remember a value (get or compute and store).
     *
     * @param string $key
     * @param int|null $ttl
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, ?int $ttl, callable $callback): mixed
    {
        $ttl = $ttl ?? config('tenancy.cache.default_ttl', self::DEFAULT_TTL);

        return Cache::remember($this->key($key), $ttl, $callback);
    }

    /**
     * Remember a value forever.
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        return Cache::rememberForever($this->key($key), $callback);
    }

    /**
     * Increment a value.
     *
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function increment(string $key, int $value = 1): int|bool
    {
        return Cache::increment($this->key($key), $value);
    }

    /**
     * Decrement a value.
     *
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function decrement(string $key, int $value = 1): int|bool
    {
        return Cache::decrement($this->key($key), $value);
    }

    /**
     * Delete a value from cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($this->key($key));
    }

    /**
     * Delete multiple values from cache.
     *
     * @param array $keys
     * @return void
     */
    public function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Flush all cache for current tenant.
     *
     * Note: This is expensive for non-Redis stores as we can't pattern-delete.
     * For file/database stores, this will log a warning.
     *
     * @return void
     */
    public function flush(): void
    {
        $tenantId = $this->tenantService->getId();

        if (!$tenantId) {
            logger()->warning('Attempted to flush tenant cache without tenant context');
            return;
        }

        $store = config('cache.default');

        if ($store === 'redis') {
            // Redis supports pattern deletion
            $this->flushRedis($tenantId);
            return;
        }

        // For other stores, we can't efficiently flush by prefix
        // Log a warning - developer should track and delete keys manually
        logger()->warning('Tenant cache flush requested but not fully supported', [
            'tenant_id' => $tenantId,
            'cache_store' => $store,
            'message' => 'Consider using Redis for full tenant cache isolation',
        ]);
    }

    /**
     * Flush Redis cache for a tenant using pattern deletion.
     *
     * @param int $tenantId
     */
    protected function flushRedis(int $tenantId): void
    {
        try {
            $redis = Cache::getRedis();
            $prefix = config('cache.prefix', 'laravel_cache');
            $pattern = "{$prefix}:tenant:{$tenantId}:*";

            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                $redis->del($keys);
            }

            logger()->info('Tenant cache flushed', [
                'tenant_id' => $tenantId,
                'keys_deleted' => count($keys),
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to flush tenant cache', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cache statistics for current tenant.
     *
     * @return array
     */
    public function stats(): array
    {
        $tenantId = $this->tenantService->getId();
        $store = config('cache.default');

        $stats = [
            'tenant_id' => $tenantId,
            'prefix' => $this->getPrefix(),
            'store' => $store,
        ];

        if ($store === 'redis') {
            try {
                $redis = Cache::getRedis();
                $prefix = config('cache.prefix', 'laravel_cache');
                $pattern = "{$prefix}:tenant:{$tenantId}:*";
                $keys = $redis->keys($pattern);

                $stats['key_count'] = count($keys);
            } catch (\Exception $e) {
                $stats['key_count'] = 'unknown';
            }
        }

        return $stats;
    }

    /**
     * Helper: Cache items list for current tenant.
     *
     * @param callable $loader Function to load items if not cached
     * @param int|null $ttl
     * @return mixed
     */
    public function items(callable $loader, ?int $ttl = null): mixed
    {
        return $this->remember('items:all', $ttl, $loader);
    }

    /**
     * Helper: Cache categories list for current tenant.
     *
     * @param callable $loader Function to load categories if not cached
     * @param int|null $ttl
     * @return mixed
     */
    public function categories(callable $loader, ?int $ttl = null): mixed
    {
        return $this->remember('categories:all', $ttl, $loader);
    }

    /**
     * Helper: Invalidate common caches after data changes.
     *
     * @param string $type Type of data changed (items, categories, orders, etc.)
     */
    public function invalidate(string $type): void
    {
        $keysToInvalidate = match ($type) {
            'items' => ['items:all', 'items:active', 'items:count'],
            'categories' => ['categories:all', 'categories:tree'],
            'orders' => ['orders:recent', 'orders:count', 'dashboard:stats'],
            'users' => ['users:all', 'users:count'],
            default => [],
        };

        $this->forgetMany($keysToInvalidate);
    }
}
