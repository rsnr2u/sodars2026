<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\ValueObjects;

class FacetResult
{
    /**
     * @param array<int, array{value: mixed, count: int}> $values
     */
    public function __construct(
        public readonly string $field,
        public readonly array $values
    ) {}

    public static function create(string $field, array $values): self
    {
        return new self($field, $values);
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'values' => $this->values,
        ];
    }
}
