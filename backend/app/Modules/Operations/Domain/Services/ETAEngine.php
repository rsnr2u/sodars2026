<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Services;

use App\Modules\Operations\Domain\ValueObjects\ETAEstimate;
use Carbon\Carbon;

class ETAEngine
{
    /**
     * Compute actual ETA details.
     */
    public function calculateETA(
        float $currentLat,
        float $currentLon,
        float $targetLat,
        float $targetLon,
        float $speedKmh
    ): ETAEstimate {
        // Haversine formula to compute distance
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($targetLat - $currentLat);
        $dLon = deg2rad($targetLon - $currentLon);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($currentLat)) * cos(deg2rad($targetLat)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceMeters = $earthRadius * $c;

        // Calculate time seconds: distance / speed
        $speedMps = max(1.0, ($speedKmh * 1000) / 3600); // minimum 1m/s
        $durationSeconds = (int) ($distanceMeters / $speedMps);

        $eta = now()->addSeconds($durationSeconds)->toDateTimeString();

        return new ETAEstimate(
            $eta,
            $distanceMeters,
            $durationSeconds
        );
    }
}
