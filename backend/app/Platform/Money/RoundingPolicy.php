<?php

declare(strict_types=1);

namespace App\Platform\Money;

enum RoundingPolicy: string
{
    case HalfUp = 'half_up';
    case Ceiling = 'ceiling';
    case Floor = 'floor';

    public function round(float $value): int
    {
        return match ($this) {
            self::HalfUp => (int) round($value, 0, PHP_ROUND_HALF_UP),
            self::Ceiling => (int) ceil($value),
            self::Floor => (int) floor($value),
        };
    }
}
