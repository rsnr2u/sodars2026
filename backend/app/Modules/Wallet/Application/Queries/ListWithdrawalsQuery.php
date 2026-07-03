<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Queries;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class ListWithdrawalsQuery
{
    public function execute(string $walletId, int $perPage = 15): LengthAwarePaginator
    {
        $wallet = Wallet::findOrFail($walletId);

        Gate::authorize('view', $wallet);

        return Withdrawal::where('wallet_id', $walletId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
