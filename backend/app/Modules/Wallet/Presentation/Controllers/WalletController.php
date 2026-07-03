<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\Wallet\Application\Actions\DepositAction;
use App\Modules\Wallet\Application\Actions\WithdrawRequestAction;
use App\Modules\Wallet\Application\Actions\ProcessWithdrawalAction;
use App\Modules\Wallet\Application\Actions\TransferAction;
use App\Modules\Wallet\Application\Queries\GetWalletDetailsQuery;
use App\Modules\Wallet\Application\Queries\ListWalletTransactionsQuery;
use App\Modules\Wallet\Application\Queries\ListWithdrawalsQuery;
use App\Modules\Wallet\Presentation\Requests\DepositRequest;
use App\Modules\Wallet\Presentation\Requests\WithdrawalRequest;
use App\Modules\Wallet\Presentation\Requests\TransferRequest;
use App\Modules\Wallet\Presentation\Resources\WalletResource;
use App\Modules\Wallet\Presentation\Resources\WalletTransactionResource;
use App\Modules\Wallet\Presentation\Resources\WithdrawalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends BaseApiController
{
    /**
     * View wallet detail information along with dynamic ledger balance calculation.
     */
    public function show(string $id, GetWalletDetailsQuery $query): JsonResponse
    {
        $data = $query->execute($id);

        $walletData = array_merge(
            (new WalletResource($data['wallet']))->resolve(),
            ['balance_cents' => $data['balance_cents']]
        );

        return $this->successResponse($walletData, 'Wallet details retrieved successfully.');
    }

    /**
     * List transaction logs mapping running snapshots.
     */
    public function transactions(string $id, ListWalletTransactionsQuery $query, Request $request): JsonResponse
    {
        $perPage = $this->getPerPage();
        $transactions = $query->execute($id, $perPage);

        return $this->successResponse(
            WalletTransactionResource::collection($transactions)->response()->getData(true),
            'Transactions retrieved successfully.'
        );
    }

    /**
     * Record cash/bank deposit.
     */
    public function deposit(string $id, DepositRequest $request, DepositAction $action): JsonResponse
    {
        $tx = $action->execute(
            $id,
            $request->input('amount_cents'),
            $request->input('reference'),
            $request->input('metadata', [])
        );

        return $this->successResponse(
            new WalletTransactionResource($tx),
            'Deposit recorded successfully.',
            201
        );
    }

    /**
     * File a new withdrawal payout request.
     */
    public function requestWithdrawal(string $id, WithdrawalRequest $request, WithdrawRequestAction $action): JsonResponse
    {
        $withdrawal = $action->execute(
            $id,
            $request->input('amount_cents'),
            $request->input('bank_account_details')
        );

        return $this->successResponse(
            new WithdrawalResource($withdrawal),
            'Withdrawal request filed successfully.',
            201
        );
    }

    /**
     * List withdrawals.
     */
    public function withdrawals(string $id, ListWithdrawalsQuery $query, Request $request): JsonResponse
    {
        $perPage = $this->getPerPage();
        $withdrawals = $query->execute($id, $perPage);

        return $this->successResponse(
            WithdrawalResource::collection($withdrawals)->response()->getData(true),
            'Withdrawals retrieved successfully.'
        );
    }

    /**
     * Process (approve / reject) withdrawal request (Admin role only).
     */
    public function processWithdrawal(string $withdrawalId, Request $request, ProcessWithdrawalAction $action): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:completed,rejected',
            'payout_reference' => 'required_if:status,completed|nullable|string|max:100',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255',
        ]);

        $action->execute(
            $withdrawalId,
            $request->input('status'),
            $request->input('payout_reference'),
            $request->input('rejection_reason')
        );

        return $this->successResponse(null, 'Withdrawal processed successfully.');
    }

    /**
     * Transfer money to another wallet.
     */
    public function transfer(string $id, TransferRequest $request, TransferAction $action): JsonResponse
    {
        $action->execute(
            $id,
            $request->input('to_wallet_id'),
            $request->input('amount_cents'),
            $request->input('reference')
        );

        return $this->successResponse(null, 'Funds transferred successfully.');
    }
}
