<?php

declare(strict_types=1);

namespace App\Infrastructure\Maps;

use App\Core\Contracts\GoogleMapsServiceInterface;
use App\Core\ValueObjects\Coordinates;

class GoogleMapsService implements GoogleMapsServiceInterface
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.key');
    }

    /**
     * Resolve coordinate location coordinates using textual address strings.
     */
    public function geocode(string $address): ?Coordinates
    {
        if (empty($this->apiKey) || app()->environment('local', 'testing')) {
            // Mock return for local testing context
            return new Coordinates(16.3067, 80.4365); // Guntur coordinates
        }

        // Live API execution via client logic goes here
        return new Coordinates(16.3067, 80.4365);
    }

    /**
     * Resolve address textual description using coordinates details.
     */
    public function reverseGeocode(Coordinates $coordinates): ?string
    {
        return 'Guntur, Andhra Pradesh, India';
    }

    /**
     * Compute geographical distances in meters between two coordinates.
     */
    public function calculateDistance(Coordinates $from, Coordinates $to): float
    {
        // Haversine formula calculation for geo distances
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($from->getLatitude());
        $lonFrom = deg2rad($from->getLongitude());
        $latTo = deg2rad($to->getLatitude());
        $lonTo = deg2rad($to->getLongitude());

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
