<?php

declare(strict_types=1);

namespace App\Platform\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bindings for shared repositories
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
