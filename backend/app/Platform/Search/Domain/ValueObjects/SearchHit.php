<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\ValueObjects;

class SearchHit
{
    public function __construct(
        public readonly string $entityId,
        public readonly string $entityType,
        public readonly array $displayData,
        public readonly float $score = 1.0,
        public readonly array $highlights = []
    ) {}

    public static function create(
        string $entityId,
        string $entityType,
        array $displayData,
        float $score = 1.0,
        array $highlights = []
    ): self {
        return new self($entityId, $entityType, $displayData, $score, $highlights);
    }

    public function toArray(): array
    {
        return [
            'entity_id' => $this->entityId,
            'entity_type' => $this->entityType,
            'display_data' => $this->displayData,
            'score' => $this->score,
            'highlights' => $this->highlights,
        ];
    }
}
