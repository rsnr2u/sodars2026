<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Repositories;

use App\Core\Contracts\BaseRepositoryInterface;
use App\Platform\Settings\Domain\Entities\SystemSetting;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a setting by its unique config key.
     */
    public function findByKey(string $key): ?SystemSetting;

    /**
     * Get all settings grouped by group name.
     */
    public function getByGroup(string $groupName): array;
}
