<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\DTOs;

use Illuminate\Http\Request;

class BranchMemberData
{
    public function __construct(
        public readonly string $userId,
        public readonly bool $isPrimary = false
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: $request->input('user_id'),
            isPrimary: (bool) $request->input('is_primary', false)
        );
    }
}
