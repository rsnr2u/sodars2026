<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Contracts\LockServiceInterface;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class LockService implements LockServiceInterface
{
    /**
     * Acquire a distributed lock.
     */
    public function acquire(string $key, int $ttlSeconds = 60): bool
    {
        return (bool) Cache::lock($key, $ttlSeconds)->get();
    }

    /**
     * Release a distributed lock.
     */
    public function release(string $key): void
    {
        Cache::lock($key)->release();
    }

    /**
     * Execute a callback wrapped in a distributed lock, ensuring release.
     */
    public function execute(string $key, callable $callback, int $ttlSeconds = 60): mixed
    {
        $lock = Cache::lock($key, $ttlSeconds);

        if (!$lock->get()) {
            throw new RuntimeException("Unable to acquire lock for key: {$key}");
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
