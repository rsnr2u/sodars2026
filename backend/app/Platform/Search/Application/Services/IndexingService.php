<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Services;

use App\Platform\Search\Domain\Entities\SearchIndex;
use App\Platform\Search\Domain\Contracts\Searchable;
use App\Platform\Search\Infrastructure\Registry\SearchProviderRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IndexingService
{
    public function __construct(
        protected SearchProviderRegistry $registry
    ) {}

    /**
     * Index a single searchable model instance.
     */
    public function index(Searchable $model): void
    {
        $indexName = $model::getSearchIndexName();
        $index = SearchIndex::where('name', $indexName)->first();

        if (!$index) {
            $index = SearchIndex::create([
                'id' => (string) Str::uuid(),
                'name' => $indexName,
                'entity_type' => get_class($model),
                'provider' => 'mysql',
                'field_mappings' => $model::getSearchFieldMappings(),
                'facet_fields' => $model::getSearchFacetFields(),
                'status' => 'ready',
            ]);
        }

        $provider = $this->registry->resolve($index->provider);
        $provider->index($indexName, (string) $model->id, get_class($model), $model->toSearchDocument());
    }

    /**
     * Remove a model instance from search index.
     */
    public function remove(string $indexName, string $entityId): void
    {
        $index = SearchIndex::where('name', $indexName)->first();
        if (!$index) {
            return;
        }

        $provider = $this->registry->resolve($index->provider);
        $provider->remove($indexName, $entityId);
    }

    /**
     * Rebuild the entire search index for a model.
     */
    public function rebuildIndex(string $indexName): void
    {
        $index = SearchIndex::where('name', $indexName)->firstOrFail();
        $index->update(['status' => 'building']);

        $entityClass = $index->entity_type;
        $provider = $this->registry->resolve($index->provider);

        try {
            $provider->deleteIndex($indexName);
            
            $index = SearchIndex::create([
                'id' => (string) Str::uuid(),
                'name' => $indexName,
                'entity_type' => $entityClass,
                'provider' => 'mysql',
                'field_mappings' => $entityClass::getSearchFieldMappings(),
                'facet_fields' => $entityClass::getSearchFacetFields(),
                'status' => 'building',
            ]);

            $chunkSize = 200;
            $entityClass::chunk($chunkSize, function ($models) use ($provider, $indexName, $entityClass) {
                $documents = [];
                foreach ($models as $model) {
                    if ($model instanceof Searchable) {
                        $documents[(string) $model->id] = $model->toSearchDocument();
                    }
                }
                if (!empty($documents)) {
                    $provider->bulkIndex($indexName, $entityClass, $documents);
                }
            });

            $index->update([
                'status' => 'ready',
                'last_rebuilt_at' => now(),
            ]);
        } catch (\Exception $e) {
            $index->update(['status' => 'error']);
            throw $e;
        }
    }
}
