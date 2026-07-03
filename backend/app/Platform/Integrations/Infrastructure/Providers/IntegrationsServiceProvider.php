<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Providers;

use App\Platform\Integrations\Domain\Contracts\WebhookSigner;
use App\Platform\Integrations\Domain\Contracts\WebhookTransport;
use App\Platform\Integrations\Infrastructure\Signers\HmacWebhookSigner;
use App\Platform\Integrations\Infrastructure\Transport\HttpWebhookTransport;
use App\Platform\Integrations\Application\Listeners\DomainEventListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class IntegrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WebhookSigner::class, HmacWebhookSigner::class);
        $this->app->singleton(WebhookTransport::class, HttpWebhookTransport::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerEventListeners();
    }

    protected function registerRoutes(): void
    {
        $routeFile = app_path('Platform/Integrations/Presentation/Routes/api.php');
        if (file_exists($routeFile)) {
            Route::middleware('api')->group($routeFile);
        }
    }

    protected function registerEventListeners(): void
    {
        Event::subscribe(DomainEventListener::class);
    }
}
