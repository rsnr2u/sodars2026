<?php

namespace App\Providers;

use App\Core\CommandBus\CommandBus;
use App\Core\Contracts\FileStorageManagerInterface;
use App\Core\Contracts\GoogleMapsServiceInterface;
use App\Core\QueryBus\QueryBus;
use App\Core\Services\CacheService;
use App\Core\Services\DateService;
use App\Core\Services\HealthService;
use App\Core\Services\ImportExportService;
use App\Core\Services\MoneyService;
use App\Core\Services\SearchService;
use App\Core\Services\StateService;
use App\Infrastructure\Maps\GoogleMapsService;
use App\Infrastructure\Storage\FileStorageManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Infrastructure Bindings
        $this->app->singleton(FileStorageManagerInterface::class, FileStorageManager::class);
        $this->app->singleton(GoogleMapsServiceInterface::class, GoogleMapsService::class);

        // Core Buses Singletons
        $this->app->singleton(CommandBus::class);
        $this->app->singleton(QueryBus::class);

        // Core Services Singletons
        $this->app->singleton(CacheService::class);
        $this->app->singleton(DateService::class);
        $this->app->singleton(MoneyService::class);
        $this->app->singleton(StateService::class);
        $this->app->singleton(SearchService::class);
        $this->app->singleton(HealthService::class);
        $this->app->singleton(ImportExportService::class);

        // Core Traces, Locks and Outbox bindings
        $this->app->singleton(\App\Core\Context\TraceContext::class);
        $this->app->singleton(\App\Core\Contracts\LockServiceInterface::class, \App\Core\Services\LockService::class);
        $this->app->singleton(\App\Core\Services\OutboxService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
