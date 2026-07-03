<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\ValueObjects;

class GeoLocation
{
    public readonly string $geoHash;

    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {
        $this->geoHash = self::encode($latitude, $longitude, 12);
    }

    /**
     * Standard geohash base32 encoding.
     */
    public static function encode(float $latitude, float $longitude, int $precision = 12): string
    {
        $base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
        $latMin = -90.0;
        $latMax = 90.0;
        $lonMin = -180.0;
        $lonMax = 180.0;
        
        $geohash = '';
        $isEven = true;
        $bit = 0;
        $ch = 0;
        
        while (strlen($geohash) < $precision) {
            if ($isEven) {
                $mid = ($lonMin + $lonMax) / 2;
                if ($longitude > $mid) {
                    $ch |= (1 << (4 - $bit));
                    $lonMin = $mid;
                } else {
                    $lonMax = $mid;
                }
            } else {
                $mid = ($latMin + $latMax) / 2;
                if ($latitude > $mid) {
                    $ch |= (1 << (4 - $bit));
                    $latMin = $mid;
                } else {
                    $latMax = $mid;
                }
            }
            
            $isEven = !$isEven;
            if ($bit < 4) {
                $bit++;
            } else {
                $geohash .= $base32[$ch];
                $bit = 0;
                $ch = 0;
            }
        }
        
        return $geohash;
    }
}
