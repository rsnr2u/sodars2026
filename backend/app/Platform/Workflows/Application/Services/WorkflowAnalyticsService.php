<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Application\Services;

class WorkflowAnalyticsService
{
    /**
     * Stub for workflow analytics metric capture.
     */
    public function recordMetric(string $instanceId, string $metricType, float $value, array $tags = []): void
    {
        // Future implementation for tracking approval durations, breach rates, etc.
    }
}
