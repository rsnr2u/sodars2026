<?php

declare(strict_types=1);

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Store item in cache. Supports tagging if driver is compatible.
     */
    public function set(string $key, mixed $value, array $tags = [], int $ttlSeconds = 86400): void
    {
        if (! empty($tags) && $this->supportsTags()) {
            Cache::tags($tags)->put($key, $value, $ttlSeconds);
        } else {
            Cache::put($key, $value, $ttlSeconds);
        }
    }

    /**
     * Retrieve item from cache.
     */
    public function get(string $key, array $tags = []): mixed
    {
        if (! empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->get($key);
        }

        return Cache::get($key);
    }

    /**
     * Clear items matching tags from cache.
     */
    public function clearTags(array $tags): void
    {
        if ($this->supportsTags()) {
            Cache::tags($tags)->flush();
        } else {
            Cache::flush();
        }
    }

    /**
     * Determine if cache store supports tagging.
     */
    protected function supportsTags(): bool
    {
        return ! in_array(config('cache.default'), ['file', 'database'], true);
    }
}
