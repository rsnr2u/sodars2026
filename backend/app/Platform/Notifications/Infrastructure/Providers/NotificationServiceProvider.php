<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Infrastructure\Providers;

use App\Platform\Notifications\Application\Services\NotificationService;
use App\Platform\Notifications\Infrastructure\Registry\ChannelRegistry;
use App\Platform\Notifications\Infrastructure\Listeners\NotificationEventListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind drivers registry as a singleton
        $this->app->singleton(ChannelRegistry::class, function () {
            return new ChannelRegistry();
        });

        // Bind core service orchestrator
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    public function boot(): void
    {
        // Load API Routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');

        // Subscribe domain event listeners
        $events = $this->app->make(Dispatcher::class);
        $listener = new NotificationEventListener($this->app->make(NotificationService::class));
        $listener->subscribe($events);
    }
}
