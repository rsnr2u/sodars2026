<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Enums;

enum ProviderStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Verified = 'verified';
    case Suspended = 'suspended';
    case Deactivated = 'deactivated';

    /**
     * Get allowed status transitions for the provider.
     *
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Draft->value => [self::Pending->value],
            self::Pending->value => [self::Verified->value, self::Draft->value],
            self::Verified->value => [self::Suspended->value, self::Deactivated->value],
            self::Suspended->value => [self::Verified->value, self::Deactivated->value],
            self::Deactivated->value => [],
        ];
    }
}
