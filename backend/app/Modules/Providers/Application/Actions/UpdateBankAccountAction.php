<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Providers\Application\DTOs\UpdateBankAccountData;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Entities\ProviderActivity;
use App\Modules\Providers\Domain\Events\ProviderBankUpdated;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class UpdateBankAccountAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Configure payout bank account parameters.
     */
    public function execute(string $providerId, UpdateBankAccountData $data): ProviderBankAccount
    {
        $this->providerReadRepo->findOrFail($providerId);

        if ($data->isPrimary) {
            ProviderBankAccount::where('provider_id', $providerId)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        /** @var ProviderBankAccount $account */
        $account = ProviderBankAccount::updateOrCreate([
            'provider_id' => $providerId,
            'account_number' => $data->accountNumber,
        ], [
            'bank_name' => $data->bankName,
            'account_holder' => $data->accountHolder,
            'routing_code' => $data->routingCode,
            'is_primary' => $data->isPrimary,
            'verification_status' => 'pending',
        ]);

        $eventData = [
            'provider_id' => $providerId,
            'bank_name' => $data->bankName,
            'account_holder' => $data->accountHolder,
            'is_primary' => $data->isPrimary,
        ];

        // 1. Record outbox
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: $providerId,
            eventName: 'provider.bank.updated.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch domain event
        Event::dispatch(new ProviderBankUpdated(
            aggregateId: $providerId,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            userId: Auth::id() ? (string) Auth::id() : null
        ));

        // 3. Log activity timeline
        ProviderActivity::create([
            'provider_id' => $providerId,
            'activity_type' => 'BankUpdated',
            'description' => "Payout bank details updated: {$data->bankName} (holder: {$data->accountHolder}).",
            'causation_id' => TraceContext::causationId(),
            'correlation_id' => TraceContext::correlationId(),
            'trace_id' => TraceContext::traceId(),
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        return $account;
    }
}
