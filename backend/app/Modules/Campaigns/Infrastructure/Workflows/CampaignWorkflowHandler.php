<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Workflows;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;

class CampaignWorkflowHandler implements WorkflowHandler
{
    public function __construct(
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function entityClass(): string
    {
        return Campaign::class;
    }

    public function workflowKey(): string
    {
        return 'campaign.approval';
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
        $campaign = $entity;
        $targetStatus = match ($transition) {
            'approve' => CampaignStatus::Approved->value,
            'reject' => CampaignStatus::Cancelled->value,
            'request_changes' => CampaignStatus::Draft->value,
            default => throw new \InvalidArgumentException("Invalid workflow transition: {$transition}"),
        };

        // Delegate status update to orchestrator
        $this->lifecycleService->transitionTo(
            $campaign,
            $targetStatus,
            ['comments' => $context->comments]
        );

        return WorkflowResult::create(
            success: true,
            previousState: $campaign->status->value ?? $campaign->status,
            newState: $targetStatus,
            metadata: ['campaign_code' => $campaign->campaign_code]
        );
    }

    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult {
        $campaign = $entity;
        $previousState = $campaign->status->value ?? $campaign->status;
        $targetStatus = $history->from_state ?? CampaignStatus::Draft->value;

        $this->lifecycleService->transitionTo(
            $campaign,
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
