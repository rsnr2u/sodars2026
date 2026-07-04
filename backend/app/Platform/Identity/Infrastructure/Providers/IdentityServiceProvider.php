<?php

declare(strict_types=1);

namespace App\Platform\Identity\Infrastructure\Providers;

use App\Platform\Identity\Domain\Contracts\PermissionResolver;
use App\Platform\Identity\Infrastructure\Security\SpatiePermissionResolver;
use App\Platform\Identity\Application\Listeners\AuthEventListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind PermissionResolver contract
        $this->app->singleton(PermissionResolver::class, SpatiePermissionResolver::class);

        // Bind IdentityContext request-scoped singleton
        $this->app->singleton(\App\Platform\Identity\Application\Services\IdentityContext::class, function () {
            return new \App\Platform\Identity\Application\Services\IdentityContext();
        });
    }

    public function boot(): void
    {
        // Load API Routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');

        // Subscribe to auth events
        $events = $this->app->make(Dispatcher::class);
        $listener = $this->app->make(AuthEventListener::class);
        $events->subscribe($listener);
    }
}
