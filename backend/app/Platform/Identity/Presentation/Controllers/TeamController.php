<?php

declare(strict_types=1);

namespace App\Platform\Identity\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Identity\Application\Services\TeamService;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Identity\Domain\Entities\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends BaseApiController
{
    public function __construct(
        protected TeamService $teamService
    ) {}

    public function index(): JsonResponse
    {
        $orgId = IdentityContext::organizationId();
        if (!$orgId) {
            return $this->successResponse([], 'Teams retrieved.');
        }

        $teams = Team::where('organization_id', $orgId)->get();
        return $this->successResponse($teams->toArray(), 'Teams retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $orgId = IdentityContext::organizationId();
        if (!$orgId) {
            return $this->errorResponse('Organization context missing.', null, 400);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = $this->teamService->create($orgId, $request->all());
        return $this->successResponse($team->toArray(), 'Team created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $team = Team::with('members.user')->findOrFail($id);

        // Enforce organization boundaries
        if ($team->organization_id !== IdentityContext::organizationId()) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        return $this->successResponse($team->toArray(), 'Team details retrieved.');
    }

    public function addMember(string $id, Request $request): JsonResponse
    {
        $team = Team::findOrFail($id);

        // Enforce organization boundaries
        if ($team->organization_id !== IdentityContext::organizationId()) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'role' => 'nullable|string|in:owner,admin,member,viewer',
        ]);

        try {
            $member = $this->teamService->addMember(
                $id,
                $request->input('user_id'),
                $request->input('role', 'member')
            );
            return $this->successResponse($member->toArray(), 'Member added to team successfully.', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function removeMember(string $id, string $userId): JsonResponse
    {
        $team = Team::findOrFail($id);

        // Enforce organization boundaries
        if ($team->organization_id !== IdentityContext::organizationId()) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        $removed = $this->teamService->removeMember($id, $userId);
        if ($removed) {
            return $this->successResponse(null, 'Member removed from team successfully.');
        }

        return $this->errorResponse('Member not found in team.', null, 404);
    }
}
