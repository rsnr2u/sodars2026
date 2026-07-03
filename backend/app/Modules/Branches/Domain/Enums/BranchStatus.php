<?php

declare(strict_types=1);

namespace App\Modules\Branches\Domain\Enums;

enum BranchStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Archived = 'archived';

    /**
     * Get allowed transitions from each state.
     *
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Active->value => [self::Inactive->value, self::Suspended->value],
            self::Inactive->value => [self::Active->value, self::Archived->value],
            self::Suspended->value => [self::Active->value, self::Archived->value],
            self::Archived->value => [],
        ];
    }
}
