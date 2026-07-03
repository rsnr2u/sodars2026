<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\ValueObjects\GeoLocation;
use Closure;

class CreateInventoryStage
{
    /**
     * Create base inventory aggregate structure.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        // 1. Generate unique code
        $categoryCode = strtoupper(substr($dto->inventoryCategory, 0, 3));
        $random = mt_rand(100000, 999999);
        $code = "INV-{$categoryCode}-{$random}";

        // 2. Compute Geohash
        $geoLocation = new GeoLocation($dto->latitude, $dto->longitude);

        $inventory = Inventory::create([
            'inventory_code' => $code,
            'display_name' => $dto->displayName,
            'provider_id' => $dto->providerId,
            'branch_id' => $passable['branch_id'],
            'country_id' => $passable['country_id'],
            'state_id' => $passable['state_id'],
            'district_id' => $passable['district_id'],
            'city_id' => $passable['city_id'],
            'pincode_id' => $passable['pincode_id'],
            'inventory_category' => $dto->inventoryCategory,
            'inventory_type' => $dto->inventoryType,
            'ownership_type' => $dto->ownershipType,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
            'geo_hash' => $geoLocation->geoHash,
            'normalized_address' => $dto->normalizedAddress,
            'search_keywords' => $dto->searchKeywords,
            'status' => 'draft',
            'marketplace_enabled' => true,
            'is_featured' => false,
            'accepts_programmatic_booking' => false,
            'visibility' => 'public',
            'ai_scores' => $dto->aiScores,
            'inventory_capabilities' => $dto->capabilities,
        ]);

        $passable['inventory'] = $inventory;

        return $next($passable);
    }
}
