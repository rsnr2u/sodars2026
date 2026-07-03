<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Modules\Branches\Domain\Entities\BranchUser;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;

class RemoveBranchMemberAction
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Mark a branch membership as inactive with left_at timestamp.
     */
    public function execute(string $branchId, string $memberId): void
    {
        $this->branchRepo->findOrFail($branchId);

        /** @var BranchUser $member */
        $member = BranchUser::findOrFail($memberId);

        if ($member->branch_id !== $branchId) {
            throw new \InvalidArgumentException('The specified member does not belong to this branch.');
        }

        $member->update([
            'is_active' => false,
            'is_primary' => false,
            'left_at' => now(),
        ]);
    }
}
