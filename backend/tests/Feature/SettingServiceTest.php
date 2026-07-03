<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Platform\Settings\Domain\Entities\SystemSetting;
use App\Platform\Settings\Domain\Services\SettingServiceInterface;
use Tests\Core\FeatureTestCase;

class SettingServiceTest extends FeatureTestCase
{
    protected SettingServiceInterface $settingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingService = app(SettingServiceInterface::class);
    }

    public function test_setting_can_be_set_and_resolved(): void
    {
        $this->settingService->set('test.config_key', 'hello_world');

        $value = $this->settingService->get('test.config_key');
        $this->assertEquals('hello_world', $value);
    }

    public function test_setting_encryption(): void
    {
        // Set an encrypted setting parameter
        $this->settingService->set('test.secure_key', 'super_secret', [
            'is_encrypted' => true,
            'group_name' => 'security',
            'category' => 'encryption',
        ]);

        // Verify the database record itself stores encrypted ciphertext
        $setting = SystemSetting::query()->where('setting_key', 'test.secure_key')->first();
        $this->assertInstanceOf(SystemSetting::class, $setting);
        $this->assertNotEquals('super_secret', $setting->getRawOriginal('setting_value'));

        // Assert decryption works automatically on model retrieval / service get
        $resolved = $this->settingService->get('test.secure_key');
        $this->assertEquals('super_secret', $resolved);
    }

    public function test_env_override_priority(): void
    {
        // Seeding database configuration with override set to true
        $this->settingService->set('test.env_key', 'database_value', [
            'is_env_override' => true,
        ]);

        // Mocking ENV variable value in runtime environment
        putenv('TEST_ENV_KEY=env_priority_value');

        $resolved = $this->settingService->get('test.env_key');
        $this->assertEquals('env_priority_value', $resolved);

        // Clear mock env
        putenv('TEST_ENV_KEY');
    }
}
