<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Workflows;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;
use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;

class ProviderWorkflowHandler implements WorkflowHandler
{
    public function __construct(
        protected ProviderLifecycleService $lifecycleService
    ) {}

    public function entityClass(): string
    {
        return Provider::class;
    }

    public function workflowKey(): string
    {
        return 'provider.verification';
    }

    public function availableTransitions(object $entity): array
    {
        return ['approve', 'reject', 'request_changes'];
    }

    public function transition(
        object $entity,
        string $transition,
        WorkflowContext $context
    ): WorkflowResult {
        $provider = $entity;
        $targetStatus = match ($transition) {
            'approve' => ProviderStatus::Verified->value,
            'reject' => ProviderStatus::Draft->value,
            'request_changes' => ProviderStatus::Draft->value,
            default => throw new \InvalidArgumentException("Invalid workflow transition: {$transition}"),
        };

        $this->lifecycleService->transitionTo(
            $provider,
            $targetStatus,
            ['comments' => $context->comments]
        );

        return WorkflowResult::create(
            success: true,
            previousState: $provider->status->value ?? $provider->status,
            newState: $targetStatus,
            metadata: ['company_name' => $provider->company_name]
        );
    }

    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult {
        $provider = $entity;
        $previousState = $provider->status->value ?? $provider->status;
        $targetStatus = $history->from_state ?? ProviderStatus::Draft->value;

        $this->lifecycleService->transitionTo(
            $provider,
            $targetStatus,
            ['comments' => 'Saga rollback compensation executed.']
        );

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $targetStatus
        );
    }
}
