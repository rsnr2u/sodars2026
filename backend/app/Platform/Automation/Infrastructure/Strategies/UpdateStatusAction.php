<?php

declare(strict_types=1);

namespace App\Platform\Automation\Infrastructure\Strategies;

use App\Platform\Automation\Domain\Contracts\AutomationActionStrategy;
use App\Platform\Automation\Domain\Entities\AutomationRule;

class UpdateStatusAction implements AutomationActionStrategy
{
    public function execute(AutomationRule $rule, array $actionParams, array $eventPayload): void
    {
        $entityType = $actionParams['entity_type'] ?? $eventPayload['entity_type'] ?? null;
        $entityId = $actionParams['entity_id'] ?? $eventPayload['aggregateId'] ?? null;
        $newStatus = $actionParams['status'] ?? null;

        if (!$entityType || !$entityId || !$newStatus) {
            return;
        }

        if (!class_exists($entityType)) {
            return;
        }

        $model = $entityType::find($entityId);
        if (!$model) {
            return;
        }

        if (method_exists($model, 'transitionTo')) {
            $model->transitionTo($newStatus);
        } else {
            $model->update(['status' => $newStatus]);
        }
    }
}
