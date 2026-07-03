<?php

declare(strict_types=1);

namespace App\Core\Traits;

trait HasSettings
{
    /**
     * Parse settings JSON configuration.
     */
    public function getSettingValue(string $key, mixed $default = null): mixed
    {
        $settings = json_decode((string) ($this->settings ?? '{}'), true);

        return $settings[$key] ?? $default;
    }
}
