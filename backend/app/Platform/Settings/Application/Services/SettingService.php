<?php

declare(strict_types=1);

namespace App\Platform\Settings\Application\Services;

use App\Core\Events\SettingsUpdated;
use App\Core\Services\CacheService;
use App\Platform\Settings\Domain\Repositories\SettingRepositoryInterface;
use App\Platform\Settings\Domain\Services\SettingServiceInterface;
use Illuminate\Support\Facades\Crypt;

class SettingService implements SettingServiceInterface
{
    protected SettingRepositoryInterface $repository;

    protected CacheService $cacheService;

    /**
     * SettingService constructor.
     */
    public function __construct(SettingRepositoryInterface $repository, CacheService $cacheService)
    {
        $this->repository = $repository;
        $this->cacheService = $cacheService;
    }

    /**
     * Resolve configuration value matching the priority rules.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting.resolve.{$key}";

        // 1. Try resolving from Cache first
        $cachedValue = $this->cacheService->get($cacheKey, ['settings']);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        // Translate dots in key to match ENV naming style (e.g., mail.host => MAIL_HOST)
        $envKey = strtoupper(str_replace('.', '_', $key));

        // 2. Resolve database setting
        $dbSetting = $this->repository->findByKey($key);

        // Priority resolution: ENV -> Database -> Default config
        $value = null;

        if ($dbSetting) {
            if ($dbSetting->is_env_override) {
                // If override is enabled, prefer ENV if set, otherwise DB
                $value = (getenv($envKey) ?: null) ?? $dbSetting->setting_value;
            } else {
                // Prefer Database value
                $value = $dbSetting->setting_value;
            }
        } else {
            // Check ENV directly, fallback to config file value
            $value = (getenv($envKey) ?: null) ?? config($key) ?? $default;
        }

        // Cache resolved parameter
        $this->cacheService->set($cacheKey, $value, ['settings']);

        return $value;
    }

    /**
     * Set/Update setting parameter in database and flush cache.
     */
    public function set(string $key, ?string $value, array $extra = []): void
    {
        $setting = $this->repository->findByKey($key);

        $isEncrypted = $extra['is_encrypted'] ?? ($setting->is_encrypted ?? false);
        $dbValue = $value;

        if ($value && $isEncrypted) {
            $dbValue = Crypt::encryptString($value);
        }

        $attributes = array_merge([
            'setting_key' => $key,
            'setting_value' => $dbValue,
            'group_name' => $extra['group_name'] ?? 'general',
            'category' => $extra['category'] ?? 'general',
            'is_encrypted' => $isEncrypted,
            'is_env_override' => $extra['is_env_override'] ?? false,
        ], $extra);

        if ($setting) {
            $this->repository->update($setting->id, $attributes);
        } else {
            $this->repository->create($attributes);
        }

        // Dispatch SettingsUpdated event
        event(new SettingsUpdated($key, $value));

        // Invalidate caching
        $this->cacheService->clearTags(['settings']);
    }
}
