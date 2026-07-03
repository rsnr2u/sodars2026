<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use InvalidArgumentException;

final class EmailAddress
{
    private string $email;

    public function __construct(string $email)
    {
        $filtered = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($filtered === false) {
            throw new InvalidArgumentException('Invalid email address format.');
        }

        $this->email = (string) $filtered;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
