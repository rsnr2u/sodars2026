<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services;

use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;

class WorkflowMetricsEngine
{
    /**
     * Compute real-time operational workflow metrics.
     */
    public function getMetrics(?string $orgId = null): array
    {
        $instanceQuery = WorkflowInstance::query();
        $taskQuery = WorkflowTask::query();

        if ($orgId) {
            $instanceQuery->where('organization_id', $orgId);
            $taskQuery->whereHas('instance', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            });
        }

        $instances = $instanceQuery->get();

        $total = $instances->count();
        $completed = $instances->where('status', WorkflowStatus::Completed)->count();
        $terminated = $instances->where('status', WorkflowStatus::Terminated)->count();
        $active = $instances->where('status', WorkflowStatus::Active)->count();

        // Calculate average duration of completed instances in seconds
        $totalDuration = 0;
        $completedCount = 0;
        foreach ($instances as $instance) {
            if ($instance->status === WorkflowStatus::Completed && $instance->completed_at && $instance->started_at) {
                $totalDuration += $instance->completed_at->getTimestamp() - $instance->started_at->getTimestamp();
                $completedCount++;
            }
        }
        $avgDuration = $completedCount > 0 ? ($totalDuration / $completedCount) : 0;

        // SLA breaches
        $slaBreaches = $taskQuery->where('status', TaskStatus::Escalated)->count();

        return [
            'total_instances' => $total,
            'active_instances' => $active,
            'completed_instances' => $completed,
            'terminated_instances' => $terminated,
            'average_duration_seconds' => $avgDuration,
            'sla_breaches' => $slaBreaches,
        ];
    }
}
