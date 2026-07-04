<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\ValueObjects;

class DeviceFingerprint
{
    public function __construct(
        public readonly string $deviceType,
        public readonly ?string $browser,
        public readonly ?string $os,
    ) {}

    public static function fromUserAgent(string $userAgent): self
    {
        $ua = strtolower($userAgent);

        // Device type
        $deviceType = 'desktop';
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            $deviceType = 'mobile';
        } elseif (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            $deviceType = 'tablet';
        } elseif (str_contains($ua, 'postman') || str_contains($ua, 'curl') || str_contains($ua, 'sodars-webhook')) {
            $deviceType = 'api';
        }

        // Browser
        $browser = null;
        if (str_contains($ua, 'firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'edg')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'safari')) {
            $browser = 'Safari';
        }

        // OS
        $os = null;
        if (str_contains($ua, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'mac os')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'Linux';
        } elseif (str_contains($ua, 'android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
            $os = 'iOS';
        }

        return new self($deviceType, $browser, $os);
    }
}
