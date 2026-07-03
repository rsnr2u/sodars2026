<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Suspended = 'suspended';
    case Inactive = 'inactive';
    case Archived = 'archived';

    /**
     * Get allowed status transitions.
     *
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Draft->value => [self::PendingApproval->value],
            self::PendingApproval->value => [self::Approved->value, self::Draft->value],
            self::Approved->value => [self::Suspended->value, self::Inactive->value, self::Archived->value],
            self::Suspended->value => [self::Approved->value],
            self::Inactive->value => [self::Approved->value],
            self::Archived->value => [],
        ];
    }
}
