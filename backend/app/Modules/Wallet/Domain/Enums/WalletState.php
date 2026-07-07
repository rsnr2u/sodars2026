<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Enums;

enum WalletState: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Frozen = 'frozen';
    case Suspended = 'suspended';
    case Closed = 'closed';

    /**
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Draft->value => [self::Active->value, self::Closed->value],
            self::Active->value => [self::Frozen->value, self::Suspended->value, self::Closed->value],
            self::Frozen->value => [self::Active->value, self::Suspended->value, self::Closed->value],
            self::Suspended->value => [self::Active->value, self::Closed->value],
            self::Closed->value => [],
        ];
    }

    public function canTransitionTo(string $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? [], true);
    }
}
