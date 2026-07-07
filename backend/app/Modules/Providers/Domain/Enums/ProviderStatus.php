<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Enums;

enum ProviderStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Verified = 'verified';
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';

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
            self::Verified->value => [self::Active->value, self::Suspended->value],
            self::Active->value => [self::Suspended->value, self::Archived->value],
            self::Suspended->value => [self::Active->value, self::Archived->value],
            self::Archived->value => [],
        ];
    }
}
