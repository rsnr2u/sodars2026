<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

use Illuminate\Http\Request;

class CreateInventoryData
{
    /**
     * @param array<string, mixed> $aiScores
     * @param array<string, mixed> $capabilities
     * @param array<int, array<string, mixed>> $faces
     * @param array<int, array<string, mixed>> $pricing
     */
    public function __construct(
        public readonly string $displayName,
        public readonly string $providerId,
        public readonly string $inventoryCategory,
        public readonly string $inventoryType,
        public readonly string $ownershipType,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly string $normalizedAddress,
        public readonly ?string $searchKeywords = null,
        public readonly array $aiScores = [],
        public readonly array $capabilities = [],
        public readonly array $faces = [],
        public readonly array $pricing = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            displayName: (string) $request->input('display_name'),
            providerId: (string) $request->input('provider_id'),
            inventoryCategory: (string) $request->input('inventory_category'),
            inventoryType: (string) $request->input('inventory_type'),
            ownershipType: (string) $request->input('ownership_type'),
            latitude: (float) $request->input('latitude'),
            longitude: (float) $request->input('longitude'),
            normalizedAddress: (string) $request->input('normalized_address'),
            searchKeywords: $request->input('search_keywords'),
            aiScores: $request->input('ai_scores', []),
            capabilities: $request->input('inventory_capabilities', []),
            faces: $request->input('faces', []),
            pricing: $request->input('pricing', [])
        );
    }
}
