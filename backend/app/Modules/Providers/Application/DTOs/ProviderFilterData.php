<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\DTOs;

use Illuminate\Http\Request;

class ProviderFilterData
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $search = null,
        public readonly ?string $branchId = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status'),
            search: $request->query('search'),
            branchId: $request->query('branch_id')
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'default_branch_id' => $this->branchId,
        ], fn ($val) => $val !== null && $val !== '');
    }
}
