<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\Contracts;

use App\Platform\Search\Domain\ValueObjects\SearchQuery;
use App\Platform\Search\Domain\ValueObjects\SearchResult;

interface SearchProvider
{
    public function search(string $indexName, SearchQuery $query): SearchResult;
    public function suggest(string $indexName, string $prefix, int $limit = 10): array;
    public function index(string $indexName, string $entityId, string $entityType, array $document): void;
    public function remove(string $indexName, string $entityId): void;
    public function bulkIndex(string $indexName, string $entityType, array $documents): void;
    public function createIndex(string $indexName, array $fieldMappings): void;
    public function deleteIndex(string $indexName): void;
}
