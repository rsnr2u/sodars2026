<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

use Illuminate\Http\Request;

class InventoryFilterData
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $providerId = null,
        public readonly ?string $branchId = null,
        public readonly ?string $inventoryCategory = null,
        public readonly ?string $inventoryType = null,
        public readonly ?string $ownershipType = null,
        public readonly ?string $cityId = null,
        public readonly ?string $stateId = null,
        public readonly ?string $pincodeId = null,
        public readonly ?string $search = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?float $radiusKm = null,
        public readonly ?bool $marketplaceEnabled = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->input('status'),
            providerId: $request->input('provider_id'),
            branchId: $request->input('branch_id'),
            inventoryCategory: $request->input('inventory_category'),
            inventoryType: $request->input('inventory_type'),
            ownershipType: $request->input('ownership_type'),
            cityId: $request->input('city_id'),
            stateId: $request->input('state_id'),
            pincodeId: $request->input('pincode_id'),
            search: $request->input('search'),
            latitude: $request->has('latitude') ? (float) $request->input('latitude') : null,
            longitude: $request->has('longitude') ? (float) $request->input('longitude') : null,
            radiusKm: $request->has('radius_km') ? (float) $request->input('radius_km') : null,
            marketplaceEnabled: $request->has('marketplace_enabled') ? (bool) $request->input('marketplace_enabled') : null
        );
    }

    /**
     * Map into array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'provider_id' => $this->providerId,
            'branch_id' => $this->branchId,
            'inventory_category' => $this->inventoryCategory,
            'inventory_type' => $this->inventoryType,
            'ownership_type' => $this->ownershipType,
            'city_id' => $this->cityId,
            'state_id' => $this->stateId,
            'pincode_id' => $this->pincodeId,
            'search' => $this->search,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_km' => $this->radiusKm,
            'marketplace_enabled' => $this->marketplaceEnabled,
        ], fn($value) => $value !== null);
    }
}
