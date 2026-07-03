<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Services;

interface SettingServiceInterface
{
    /**
     * Resolve configuration value matching the priority rules.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set/Update setting parameter in database and flush cache.
     */
    public function set(string $key, ?string $value, array $extra = []): void;
}
