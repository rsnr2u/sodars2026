<?php

declare(strict_types=1);

namespace App\Platform\Automation\Application\Services;

class AutomationAnalyticsService
{
    /**
     * Stub for automation analytics metric capture.
     */
    public function recordMetric(string $ruleId, string $metricType, float $value, array $tags = []): void
    {
        // Future implementation for tracking rule executions, fail rates, execution times, etc.
    }
}
