<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

use Illuminate\Http\Request;

class UpdateInventoryData
{
    /**
     * @param array<string, mixed>|null $aiScores
     * @param array<string, mixed>|null $capabilities
     */
    public function __construct(
        public readonly ?string $displayName = null,
        public readonly ?string $inventoryCategory = null,
        public readonly ?string $inventoryType = null,
        public readonly ?string $ownershipType = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $normalizedAddress = null,
        public readonly ?string $searchKeywords = null,
        public readonly ?array $aiScores = null,
        public readonly ?array $capabilities = null,
        public readonly ?bool $marketplaceEnabled = null,
        public readonly ?string $visibility = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            displayName: $request->has('display_name') ? (string) $request->input('display_name') : null,
            inventoryCategory: $request->has('inventory_category') ? (string) $request->input('inventory_category') : null,
            inventoryType: $request->has('inventory_type') ? (string) $request->input('inventory_type') : null,
            ownershipType: $request->has('ownership_type') ? (string) $request->input('ownership_type') : null,
            latitude: $request->has('latitude') ? (float) $request->input('latitude') : null,
            longitude: $request->has('longitude') ? (float) $request->input('longitude') : null,
            normalizedAddress: $request->has('normalized_address') ? (string) $request->input('normalized_address') : null,
            searchKeywords: $request->input('search_keywords'),
            aiScores: $request->input('ai_scores'),
            capabilities: $request->input('inventory_capabilities'),
            marketplaceEnabled: $request->has('marketplace_enabled') ? (bool) $request->input('marketplace_enabled') : null,
            visibility: $request->input('visibility')
        );
    }

    /**
     * Convert to array, filtering out nulls.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->displayName !== null) $data['display_name'] = $this->displayName;
        if ($this->inventoryCategory !== null) $data['inventory_category'] = $this->inventoryCategory;
        if ($this->inventoryType !== null) $data['inventory_type'] = $this->inventoryType;
        if ($this->ownershipType !== null) $data['ownership_type'] = $this->ownershipType;
        if ($this->latitude !== null) $data['latitude'] = $this->latitude;
        if ($this->longitude !== null) $data['longitude'] = $this->longitude;
        if ($this->normalizedAddress !== null) $data['normalized_address'] = $this->normalizedAddress;
        if ($this->searchKeywords !== null) $data['search_keywords'] = $this->searchKeywords;
        if ($this->aiScores !== null) $data['ai_scores'] = $this->aiScores;
        if ($this->capabilities !== null) $data['inventory_capabilities'] = $this->capabilities;
        if ($this->marketplaceEnabled !== null) $data['marketplace_enabled'] = $this->marketplaceEnabled;
        if ($this->visibility !== null) $data['visibility'] = $this->visibility;

        return $data;
    }
}
