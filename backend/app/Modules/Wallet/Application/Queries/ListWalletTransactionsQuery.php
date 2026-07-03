<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Queries;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class ListWalletTransactionsQuery
{
    public function execute(string $walletId, int $perPage = 15): LengthAwarePaginator
    {
        $wallet = Wallet::findOrFail($walletId);

        Gate::authorize('view', $wallet);

        return WalletTransaction::where('wallet_id', $walletId)
            ->with('journal')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
