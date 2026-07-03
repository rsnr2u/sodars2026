<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\ValueObjects;

class SearchQuery
{
    protected ?string $term = null;
    protected ?string $indexName = null;
    protected array $filters = [];
    protected array $facets = [];
    protected ?string $sortField = null;
    protected string $sortDirection = 'desc';
    protected int $page = 1;
    protected int $perPage = 15;

    public function __construct(?string $term = null)
    {
        $this->term = $term;
    }

    public static function create(?string $term = null): self
    {
        return new self($term);
    }

    public function inIndex(string $indexName): self
    {
        $this->indexName = $indexName;
        return $this;
    }

    public function filterBy(string $key, mixed $value): self
    {
        $this->filters[$key] = $value;
        return $this;
    }

    public function facetOn(array $fields): self
    {
        $this->facets = $fields;
        return $this;
    }

    public function sortBy(string $field, string $direction = 'desc'): self
    {
        $this->sortField = $field;
        $this->sortDirection = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        return $this;
    }

    public function page(int $page, int $perPage = 15): self
    {
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);
        return $this;
    }

    public function getTerm(): ?string
    {
        return $this->term;
    }

    public function getIndexName(): ?string
    {
        return $this->indexName;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFacets(): array
    {
        return $this->facets;
    }

    public function getSortField(): ?string
    {
        return $this->sortField;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function toArray(): array
    {
        return [
            'term' => $this->term,
            'index_name' => $this->indexName,
            'filters' => $this->filters,
            'facets' => $this->facets,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ];
    }

    public static function fromArray(array $data): self
    {
        $query = new self($data['term'] ?? null);
        if (isset($data['index_name'])) {
            $query->inIndex($data['index_name']);
        }
        foreach ($data['filters'] ?? [] as $key => $val) {
            $query->filterBy($key, $val);
        }
        if (isset($data['facets'])) {
            $query->facetOn($data['facets']);
        }
        if (isset($data['sort_field'])) {
            $query->sortBy($data['sort_field'], $data['sort_direction'] ?? 'desc');
        }
        $query->page($data['page'] ?? 1, $data['per_page'] ?? 15);
        return $query;
    }
}
