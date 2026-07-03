<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class InvalidStatusTransitionException extends BusinessException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Transition from state [{$from}] to [{$to}] is illegal.", 422);
    }
}
