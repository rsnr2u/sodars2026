<?php

declare(strict_types=1);

namespace App\Platform\Audit\Application\Services;

use App\Platform\Audit\Domain\Enums\RiskLevel;
use App\Platform\Audit\Domain\Enums\EventCategory;

class RiskResolver
{
    /**
     * Resolve risk level based on category, event type, and optionally attributes.
     */
    public static function resolve(EventCategory $category, string $eventType, ?array $before = null, ?array $after = null): RiskLevel
    {
        // 1. Critical Actions
        if ($eventType === 'user.role_assigned' && (isset($after['role']) && $after['role'] === 'super_admin')) {
            return RiskLevel::Critical;
        }

        if ($category === EventCategory::Financial && str_contains($eventType, 'delete')) {
            return RiskLevel::Critical;
        }

        // 2. High Actions
        if ($category === EventCategory::Authorization || $category === EventCategory::Security) {
            return RiskLevel::High;
        }

        if ($category === EventCategory::Financial && (str_contains($eventType, 'post') || str_contains($eventType, 'adjust'))) {
            return RiskLevel::High;
        }

        if ($eventType === 'user.password_reset') {
            return RiskLevel::High;
        }

        // 3. Medium Actions
        if ($category === EventCategory::Workflow && str_contains($eventType, 'complete')) {
            return RiskLevel::Medium;
        }

        if ($category === EventCategory::Integration && str_contains($eventType, 'fail')) {
            return RiskLevel::Medium;
        }

        if ($eventType === 'model.deleted') {
            return RiskLevel::Medium;
        }

        // 4. Fallback to Low
        return RiskLevel::Low;
    }
}
