<?php

declare(strict_types=1);

namespace App\Platform\Settings\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Platform\Settings\Domain\Entities\SystemSetting;
use App\Platform\Settings\Domain\Repositories\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    /**
     * SettingRepository constructor.
     */
    public function __construct(SystemSetting $model)
    {
        parent::__construct($model);
    }

    public function findByKey(string $key): ?SystemSetting
    {
        $setting = $this->model->where('setting_key', $key)->first();
        return $setting instanceof SystemSetting ? $setting : null;
    }

    /**
     * Get all settings grouped by group name.
     */
    public function getByGroup(string $groupName): array
    {
        return $this->model->where('group_name', $groupName)->get()->all();
    }
}
