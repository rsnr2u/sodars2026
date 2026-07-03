<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ProviderSettings implements CastsAttributes
{
    public function __construct(
        public readonly bool $marketplaceEnabled = true,
        public readonly bool $bookingNotifications = true,
        public readonly bool $email = true,
        public readonly bool $sms = false
    ) {}

    /**
     * Cast the stored JSON database value to the ProviderSettings value object.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): self
    {
        if (empty($value)) {
            return new self();
        }

        $data = json_decode((string) $value, true) ?: [];

        return new self(
            marketplaceEnabled: (bool) ($data['marketplace_enabled'] ?? true),
            bookingNotifications: (bool) ($data['booking_notifications'] ?? true),
            email: (bool) ($data['email'] ?? true),
            sms: (bool) ($data['sms'] ?? false)
        );
    }

    /**
     * Transform the ProviderSettings value object back to database JSON.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof self) {
            return json_encode([
                'marketplace_enabled' => $value->marketplaceEnabled,
                'booking_notifications' => $value->bookingNotifications,
                'email' => $value->email,
                'sms' => $value->sms,
            ]);
        }

        if (is_array($value)) {
            return json_encode([
                'marketplace_enabled' => (bool) ($value['marketplace_enabled'] ?? true),
                'booking_notifications' => (bool) ($value['booking_notifications'] ?? true),
                'email' => (bool) ($value['email'] ?? true),
                'sms' => (bool) ($value['sms'] ?? false),
            ]);
        }

        return null;
    }
}
