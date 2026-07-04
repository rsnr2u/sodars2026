<?php

declare(strict_types=1);

namespace App\Platform\Identity\Application\Services;

use App\Platform\Identity\Domain\Entities\Team;
use App\Platform\Identity\Domain\Entities\TeamMember;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Identity\Domain\Enums\TeamRole;
use Illuminate\Support\Str;

class TeamService
{
    public function create(string $organizationId, array $data): Team
    {
        return Team::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $organizationId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);
    }

    public function addMember(string $teamId, string $userId, string $role = 'member'): TeamMember
    {
        $team = Team::findOrFail($teamId);

        // Validate user is an org member
        $isMember = OrganizationMember::where('organization_id', $team->organization_id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        if (!$isMember) {
            throw new \DomainException('User must be an organization member before joining a team.');
        }

        return TeamMember::create([
            'id' => (string) Str::uuid(),
            'team_id' => $teamId,
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function removeMember(string $teamId, string $userId): bool
    {
        return TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }
}
