<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\DTOs;

use Illuminate\Http\Request;

class AddStaffData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly bool $isPrimary = false
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            isPrimary: (bool) $request->input('is_primary', false)
        );
    }
}
