<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {}
}
