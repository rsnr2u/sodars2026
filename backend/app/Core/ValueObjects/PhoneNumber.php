<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use InvalidArgumentException;

final class PhoneNumber
{
    private string $number;

    public function __construct(string $number)
    {
        $cleaned = preg_replace('/\s+/', '', $number);
        if (! preg_match('/^\+?[1-9]\d{1,14}$/', $cleaned)) {
            throw new InvalidArgumentException('Invalid phone number format. Must match E.164 specification.');
        }

        $this->number = $cleaned;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
