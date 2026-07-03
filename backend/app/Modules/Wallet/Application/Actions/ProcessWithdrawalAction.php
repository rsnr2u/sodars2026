<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Actions;

use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Services\WalletService;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ProcessWithdrawalAction
{
    public function __construct(protected WalletService $walletService) {}

    public function execute(string $withdrawalId, string $status, ?string $payoutReference = null, ?string $rejectionReason = null): void
    {
        $withdrawal = Withdrawal::findOrFail($withdrawalId);

        Gate::authorize('update', $withdrawal->wallet);

        if ($status === 'completed') {
            if (empty($payoutReference)) {
                throw new InvalidArgumentException("Payout reference is required to complete withdrawal.");
            }
            $this->walletService->completeWithdrawal($withdrawal, $payoutReference);
        } elseif ($status === 'rejected') {
            $this->walletService->rejectWithdrawal($withdrawal, $rejectionReason ?? 'Rejected by system administrator.');
        } else {
            throw new InvalidArgumentException("Invalid target transition status: {$status}");
        }
    }
}
