<?php

declare(strict_types=1);

namespace App\Platform\Audit\Infrastructure\Providers;

use App\Platform\Audit\Domain\Contracts\AuditLogger;
use App\Platform\Audit\Application\Services\AuditService;
use App\Platform\Audit\Application\Listeners\AuditEventListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class, AuditService::class);
        $this->app->singleton(AuditService::class);
    }

    public function boot(): void
    {
        // Load API Routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');

        // Subscribe audit event listener
        $events = $this->app->make(Dispatcher::class);
        $listener = $this->app->make(AuditEventListener::class);
        $events->subscribe($listener);
    }
}
