<?php

declare(strict_types=1);

namespace App\Platform\Automation\Infrastructure\Strategies;

use App\Platform\Automation\Domain\Contracts\AutomationActionStrategy;
use App\Platform\Automation\Domain\Entities\AutomationRule;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;

class StartWorkflowAction implements AutomationActionStrategy
{
    public function __construct(
        protected WorkflowEngineService $workflowEngine
    ) {}

    public function execute(AutomationRule $rule, array $actionParams, array $eventPayload): void
    {
        $definitionKey = $actionParams['workflow_key'] ?? null;
        $entityType = $actionParams['entity_type'] ?? $eventPayload['entity_type'] ?? null;
        $entityId = $actionParams['entity_id'] ?? $eventPayload['aggregateId'] ?? null;
        $userId = $actionParams['user_id'] ?? $eventPayload['userId'] ?? null;
        $context = $actionParams['context'] ?? $eventPayload ?? [];

        if (!$definitionKey || !$entityType || !$entityId) {
            return;
        }

        $this->workflowEngine->start($definitionKey, $entityType, $entityId, $context, $userId);
    }
}
