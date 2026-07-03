<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Exceptions\InvalidStatusTransitionException;

class StateService
{
    /**
     * Validate and transition status. Throws InvalidStatusTransitionException if illegal.
     */
    public function validateTransition(string $currentStatus, string $newStatus, array $allowedTransitions): void
    {
        $allowed = $allowedTransitions[$currentStatus] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidStatusTransitionException($currentStatus, $newStatus);
        }
    }
}
