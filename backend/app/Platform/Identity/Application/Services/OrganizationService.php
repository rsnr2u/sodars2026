<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Services;

use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Identity\Domain\Enums\MembershipStatus;
use Illuminate\Support\Str;

class OrganizationService
{
    public function create(array $data): Organization
    {
        return Organization::create([
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'domain' => $data['domain'] ?? null,
            'is_active' => true,
        ]);
    }

    public function addMember(string $organizationId, string $userId, string $role = 'member'): OrganizationMember
    {
        return OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'role' => $role,
            'status' => MembershipStatus::Active->value,
            'joined_at' => now(),
        ]);
    }

    public function removeMember(string $organizationId, string $userId): bool
    {
        return OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function getMembers(string $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return OrganizationMember::where('organization_id', $organizationId)
            ->with('user')
            ->get();
    }
}
