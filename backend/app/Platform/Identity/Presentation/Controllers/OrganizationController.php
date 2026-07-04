<?php

declare(strict_types=1);

namespace App\Platform\Identity\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Identity\Application\Services\OrganizationService;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Identity\Domain\Entities\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends BaseApiController
{
    public function __construct(
        protected OrganizationService $organizationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Gated to super_admin or returning organization user belongs to
        if ($request->user()?->hasRole('super_admin')) {
            return $this->successResponse(Organization::all()->toArray(), 'Organizations retrieved.');
        }

        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $org = Organization::find($orgId);
            return $this->successResponse([$org->toArray()], 'Organizations retrieved.');
        }

        return $this->successResponse([], 'Organizations retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:organizations,slug',
            'domain' => 'nullable|string|max:255',
        ]);

        $org = $this->organizationService->create($request->all());

        // Automatically add current user as owner
        if ($request->user()) {
            $this->organizationService->addMember($org->id, (string) $request->user()->id, 'owner');
        }

        return $this->successResponse($org->toArray(), 'Organization created successfully.', 201);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        // Enforce tenant boundary
        $user = $request->user();
        if ($user && !$user->hasRole('super_admin') && IdentityContext::organizationId() !== $id) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        $org = Organization::findOrFail($id);
        return $this->successResponse($org->toArray(), 'Organization retrieved.');
    }

    public function getMembers(string $id, Request $request): JsonResponse
    {
        // Enforce tenant boundary
        $user = $request->user();
        if ($user && !$user->hasRole('super_admin') && IdentityContext::organizationId() !== $id) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        $members = $this->organizationService->getMembers($id);
        return $this->successResponse($members->toArray(), 'Organization members retrieved.');
    }

    public function addMember(string $id, Request $request): JsonResponse
    {
        // Enforce tenant boundary
        $user = $request->user();
        if ($user && !$user->hasRole('super_admin') && IdentityContext::organizationId() !== $id) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'role' => 'nullable|string|in:owner,admin,member',
        ]);

        $member = $this->organizationService->addMember(
            $id,
            $request->input('user_id'),
            $request->input('role', 'member')
        );

        return $this->successResponse($member->toArray(), 'Member added to organization successfully.', 201);
    }

    public function removeMember(string $id, string $userId, Request $request): JsonResponse
    {
        // Enforce tenant boundary
        $user = $request->user();
        if ($user && !$user->hasRole('super_admin') && IdentityContext::organizationId() !== $id) {
            return $this->errorResponse('Access denied.', null, 403);
        }

        $removed = $this->organizationService->removeMember($id, $userId);
        if ($removed) {
            return $this->successResponse(null, 'Member removed from organization successfully.');
        }

        return $this->errorResponse('Member not found in organization.', null, 404);
    }
}
