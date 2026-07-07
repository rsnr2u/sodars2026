<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Services;

use App\Modules\IoT\Domain\Entities\Device;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class HmacAuthenticator
{
    /**
     * Authenticate and authorize a incoming payload signature.
     */
    public function authenticate(
        string $serialNumber,
        string $timestamp,
        string $nonce,
        string $signature,
        string $rawPayload
    ): Device {
        // 1. Clock Skew Check (5 minute skew limit)
        $requestTime = (int) $timestamp;
        $currentTime = time();

        if (abs($currentTime - $requestTime) > 300) {
            throw new RuntimeException("Request skew is too high. Skew: " . abs($currentTime - $requestTime) . " seconds.");
        }

        // 2. Replay Protection (Block duplicate nonces in cache)
        $cacheKey = "iot:nonce:{$serialNumber}:{$nonce}";
        if (Cache::has($cacheKey)) {
            throw new RuntimeException("Duplicate request nonce detected. Replay attack blocked.");
        }
        Cache::put($cacheKey, true, 600); // Keep nonce in cache for 10 minutes

        // 3. Resolve Device & Secret Key
        $device = Device::where('serial_number', $serialNumber)->first();
        if (!$device) {
            throw new RuntimeException("Device not found with serial number: {$serialNumber}");
        }

        // 4. Compute Signature & Compare
        $expectedSignature = hash_hmac(
            'sha256',
            "{$timestamp}.{$nonce}.{$rawPayload}",
            $device->device_secret
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException("HMAC signature verification failed.");
        }

        return $device;
    }
}
