<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use App\Core\ValueObjects\Coordinates;

interface GoogleMapsServiceInterface
{
    /**
     * Resolve coordinate location coordinates using textual address strings.
     */
    public function geocode(string $address): ?Coordinates;

    /**
     * Resolve address textual description using coordinates details.
     */
    public function reverseGeocode(Coordinates $coordinates): ?string;

    /**
     * Compute geographical distances in meters between two coordinates.
     */
    public function calculateDistance(Coordinates $from, Coordinates $to): float;
}
