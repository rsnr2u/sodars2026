<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\DTOs;

use Illuminate\Http\Request;

class BranchFilterData
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $search = null,
        public readonly ?string $timezone = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status'),
            search: $request->query('search'),
            timezone: $request->query('timezone')
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'timezone' => $this->timezone,
        ], fn ($val) => $val !== null && $val !== '');
    }
}
