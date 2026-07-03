<?php

declare(strict_types=1);

namespace App\Core\Contracts;

interface LockServiceInterface
{
    /**
     * Acquire a distributed lock. Returns true if acquired.
     */
    public function acquire(string $key, int $ttlSeconds = 60): bool;

    /**
     * Release a distributed lock.
     */
    public function release(string $key): void;

    /**
     * Execute a callback wrapped in a distributed lock, ensuring release.
     */
    public function execute(string $key, callable $callback, int $ttlSeconds = 60): mixed;
}
