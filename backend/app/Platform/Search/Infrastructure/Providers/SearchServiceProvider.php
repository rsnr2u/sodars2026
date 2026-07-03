<?php

declare(strict_types=1);

namespace App\Platform\Search\Infrastructure\Providers;

use App\Platform\Search\Application\Services\SearchService;
use App\Platform\Search\Application\Services\IndexingService;
use App\Platform\Search\Application\Services\SearchAnalyticsService;
use App\Platform\Search\Application\Services\SavedSearchService;
use App\Platform\Search\Infrastructure\Registry\SearchProviderRegistry;
use App\Platform\Search\Infrastructure\Providers\MysqlFullTextProvider;
use App\Platform\Search\Application\Listeners\SearchIndexListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchProviderRegistry::class, function ($app) {
            $registry = new SearchProviderRegistry();
            $registry->register('mysql', MysqlFullTextProvider::class);
            return $registry;
        });

        $this->app->singleton(SearchService::class, function ($app) {
            return new SearchService(
                $app->make(SearchProviderRegistry::class),
                $app->make(SearchAnalyticsService::class)
            );
        });

        $this->app->singleton(IndexingService::class, function ($app) {
            return new IndexingService(
                $app->make(SearchProviderRegistry::class)
            );
        });

        $this->app->singleton(SearchAnalyticsService::class, function ($app) {
            return new SearchAnalyticsService();
        });

        $this->app->singleton(SavedSearchService::class, function ($app) {
            return new SavedSearchService();
        });
    }

    public function boot(): void
    {
        // Load API Routes
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');

        // Subscribe events to update indices
        $events = $this->app->make(Dispatcher::class);
        $listener = new SearchIndexListener();
        $listener->subscribe($events);

        // Auto-register indices
        $this->registerSearchIndex('inventories', \App\Modules\Inventory\Domain\Entities\Inventory::class);
        $this->registerSearchIndex('bookings', \App\Modules\Bookings\Domain\Entities\Booking::class);
        $this->registerSearchIndex('invoices', \App\Modules\Finance\Domain\Entities\Invoice::class);
    }

    protected function registerSearchIndex(string $name, string $entityClass): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('search_indexes')) {
                return;
            }

            \App\Platform\Search\Domain\Entities\SearchIndex::firstOrCreate(
                ['name' => $name],
                [
                    'entity_type' => $entityClass,
                    'provider' => 'mysql',
                    'field_mappings' => $entityClass::getSearchFieldMappings(),
                    'facet_fields' => $entityClass::getSearchFacetFields(),
                    'status' => 'ready',
                ]
            );
        } catch (\Exception $e) {
            // Fail silently if DB not migrated yet
        }
    }
}
