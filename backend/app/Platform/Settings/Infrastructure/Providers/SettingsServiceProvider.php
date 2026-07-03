<?php

declare(strict_types=1);

namespace App\Platform\Settings\Infrastructure\Providers;

use App\Platform\Settings\Application\Services\SettingService;
use App\Platform\Settings\Domain\Repositories\SettingRepositoryInterface;
use App\Platform\Settings\Domain\Services\SettingServiceInterface;
use App\Platform\Settings\Infrastructure\Repositories\SettingRepository;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(SettingServiceInterface::class, SettingService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations from platform module
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Load api routes
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
