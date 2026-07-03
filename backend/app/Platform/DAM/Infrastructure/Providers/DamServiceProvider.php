<?php

declare(strict_types=1);

namespace App\Platform\DAM\Infrastructure\Providers;

use App\Platform\DAM\Domain\Contracts\StorageProvider;
use App\Platform\DAM\Domain\Contracts\ImageConversionStrategy;
use App\Platform\DAM\Infrastructure\Storage\LocalStorage;
use App\Platform\DAM\Infrastructure\Image\GdConversion;
use App\Platform\DAM\Presentation\Policies\AssetPolicy;
use App\Platform\DAM\Domain\Entities\Asset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DamServiceProvider extends ServiceProvider
{
    /**
     * Register platform services.
     */
    public function register(): void
    {
        // Bind abstract interfaces to concrete drivers
        $this->app->singleton(StorageProvider::class, LocalStorage::class);
        $this->app->singleton(ImageConversionStrategy::class, GdConversion::class);
    }

    /**
     * Bootstrap platform services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Asset::class, AssetPolicy::class);

        // Load api routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
