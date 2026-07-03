<?php

declare(strict_types=1);

namespace App\Platform\Automation\Infrastructure\Strategies;

use App\Platform\Automation\Domain\Contracts\AutomationActionStrategy;
use App\Platform\Automation\Domain\Entities\AutomationRule;
use App\Platform\Notifications\Application\Services\NotificationService;

class SendNotificationAction implements AutomationActionStrategy
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(AutomationRule $rule, array $actionParams, array $eventPayload): void
    {
        $userId = $actionParams['user_id'] ?? $eventPayload['userId'] ?? null;
        $templateKey = $actionParams['template_key'] ?? null;
        $context = $actionParams['context'] ?? $eventPayload ?? [];

        if (!$userId || !$templateKey) {
            return;
        }

        $this->notificationService->send($userId, $templateKey, $context);
    }
}
