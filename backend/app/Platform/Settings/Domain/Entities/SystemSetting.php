<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Class SystemSetting
 *
 * @property string $id
 * @property string $setting_key
 * @property string|null $setting_value
 * @property string $group_name
 * @property string $category
 * @property bool $is_encrypted
 * @property bool $is_env_override
 */
class SystemSetting extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'system_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'group_name',
        'category',
        'is_encrypted',
        'is_env_override',
    ];

    /**
     * Accessor to decrypt setting value if encrypted.
     */
    public function getSettingValueAttribute(?string $value): ?string
    {
        if ($value && $this->is_encrypted) {
            try {
                return Crypt::decryptString($value);
            } catch (Throwable $e) {
                return $value;
            }
        }

        return $value;
    }
}
