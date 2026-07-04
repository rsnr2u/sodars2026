<?php

declare(strict_types=1);

namespace App\Platform\Audit\Application\Listeners;

use App\Platform\Audit\Domain\Contracts\AuditLogger;
use App\Platform\Audit\Domain\ValueObjects\AuditEnvelope;
use App\Platform\Identity\Domain\Events\UserLoggedIn;
use App\Platform\Identity\Domain\Events\UserLoggedOut;
use App\Platform\Workflows\Domain\Events\WorkflowStarted;
use App\Platform\Workflows\Domain\Events\WorkflowCompleted;
use App\Platform\Workflows\Domain\Events\WorkflowCancelled;
use Illuminate\Contracts\Events\Dispatcher;

class AuditEventListener
{
    public function __construct(
        protected AuditLogger $logger
    ) {}

    public function onUserLogin(UserLoggedIn $event): void
    {
        $envelope = AuditEnvelope::make('user.login', "User logged in from IP {$event->ipAddress}")
            ->actor($event->userId, null);

        $envelope->ipAddress = $event->ipAddress;
        $envelope->userAgent = $event->userAgent;

        $this->logger->log($envelope);
    }

    public function onUserLogout(UserLoggedOut $event): void
    {
        $envelope = AuditEnvelope::make('user.logout', "User logged out")
            ->actor($event->userId, null);

        $this->logger->log($envelope);
    }

    public function onWorkflowStarted($event): void
    {
        // Dynamic event parsing to support loose mapping if workflow module isn't loaded or booted
        $workflowId = $event->workflowInstance->id ?? 'unknown';
        $definitionName = $event->workflowInstance->definition->name ?? 'unknown';

        $envelope = AuditEnvelope::make('workflow.started', "Workflow '{$definitionName}' (ID: {$workflowId}) started")
            ->organization($event->workflowInstance->organization_id ?? null)
            ->metadata([
                'workflow_instance_id' => $workflowId,
                'definition_name' => $definitionName,
            ]);

        $this->logger->log($envelope);
    }

    public function onWorkflowCompleted($event): void
    {
        $workflowId = $event->workflowInstance->id ?? 'unknown';
        $definitionName = $event->workflowInstance->definition->name ?? 'unknown';

        $envelope = AuditEnvelope::make('workflow.completed', "Workflow '{$definitionName}' (ID: {$workflowId}) completed successfully")
            ->organization($event->workflowInstance->organization_id ?? null)
            ->metadata([
                'workflow_instance_id' => $workflowId,
                'definition_name' => $definitionName,
            ]);

        $this->logger->log($envelope);
    }

    public function subscribe(Dispatcher $events): array
    {
        $subs = [
            UserLoggedIn::class => 'onUserLogin',
            UserLoggedOut::class => 'onUserLogout',
        ];

        // Register workflow events if classes exist
        if (class_exists(WorkflowStarted::class)) {
            $subs[WorkflowStarted::class] = 'onWorkflowStarted';
        }
        if (class_exists(WorkflowCompleted::class)) {
            $subs[WorkflowCompleted::class] = 'onWorkflowCompleted';
        }

        return $subs;
    }
}
