<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Modules\Branches\Application\DTOs\BranchMemberData;
use App\Modules\Branches\Domain\Entities\BranchUser;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AssignBranchMemberAction
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Add or update a user membership to a branch.
     */
    public function execute(string $branchId, BranchMemberData $data): BranchUser
    {
        $this->branchRepo->findOrFail($branchId);

        if ($data->isPrimary) {
            // A user can only be the active manager of one branch at a time
            $otherBranchExists = BranchUser::where('user_id', $data->userId)
                ->where('branch_id', '!=', $branchId)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->exists();

            if ($otherBranchExists) {
                throw ValidationException::withMessages([
                    'user_id' => ['This user is already registered as primary manager of another branch.'],
                ]);
            }

            // Unset current primary managers of this branch
            BranchUser::where('branch_id', $branchId)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        /** @var BranchUser $member */
        $member = BranchUser::updateOrCreate([
            'branch_id' => $branchId,
            'user_id' => $data->userId,
        ], [
            'is_primary' => $data->isPrimary,
            'is_active' => true,
            'joined_at' => now(),
            'left_at' => null,
        ]);

        return $member;
    }
}
