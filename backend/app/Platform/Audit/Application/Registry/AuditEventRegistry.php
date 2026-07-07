<?php

declare(strict_types=1);

namespace App\Platform\Audit\Application\Registry;

use App\Platform\Audit\Domain\Enums\EventCategory;

class AuditEventRegistry
{
    /**
     * Map event types to their EventCategory.
     *
     * @var array<string, EventCategory>
     */
    private static array $mappings = [
        // Auth events
        'user.login' => EventCategory::Authentication,
        'user.logout' => EventCategory::Authentication,
        'user.password_reset' => EventCategory::Authorization,
        'user.role_assigned' => EventCategory::Authorization,

        // Workflow events
        'workflow.started' => EventCategory::Workflow,
        'workflow.completed' => EventCategory::Workflow,
        'workflow.cancelled' => EventCategory::Workflow,
        'workflow.task_assigned' => EventCategory::Workflow,
        'workflow.task_completed' => EventCategory::Workflow,

        // Integration events
        'webhook.delivered' => EventCategory::Integration,
        'webhook.failed' => EventCategory::Integration,
        'api_key.authenticated' => EventCategory::Integration,

        // Reporting events
        'report.generated' => EventCategory::Reporting,
        'report.exported' => EventCategory::Reporting,

        // Business events
        'booking.*' => EventCategory::Business,
        'inventory.*' => EventCategory::Business,
        'crm.*' => EventCategory::Business,
        'finance.invoice.*' => EventCategory::Business,
        'finance.payment.*' => EventCategory::Business,
        'finance.accounting.*' => EventCategory::Business,
        'finance.revenue.*' => EventCategory::Business,
        'provider.profile.*' => EventCategory::Business,
        'provider.documents.*' => EventCategory::Business,
        'provider.bank.*' => EventCategory::Business,
        'provider.subscription.*' => EventCategory::Business,
        'campaign.profile.*' => EventCategory::Business,
        'campaign.creatives.*' => EventCategory::Business,
        'campaign.proofs.*' => EventCategory::Business,
        'campaign.schedule.*' => EventCategory::Business,
        'campaign.activity.*' => EventCategory::Business,
        'wallet.profile.*' => EventCategory::Business,
        'wallet.transactions.*' => EventCategory::Business,
        'wallet.withdrawals.*' => EventCategory::Business,
        'wallet.settlements.*' => EventCategory::Business,
        'wallet.activity.*' => EventCategory::Business,
        'transport.vehicles.*' => EventCategory::Business,
        'transport.drivers.*' => EventCategory::Business,
        'transport.routes.*' => EventCategory::Business,

        // General fallback
        'model.created' => EventCategory::DataChange,
        'model.updated' => EventCategory::DataChange,
        'model.deleted' => EventCategory::DataChange,
    ];

    /**
     * Dynamic registration.
     */
    public static function register(string $eventType, EventCategory $category): void
    {
        self::$mappings[$eventType] = $category;
    }

    /**
     * Resolve category for event type, fallback to System.
     */
    public static function resolveCategory(string $eventType): EventCategory
    {
        // Try exact match
        if (isset(self::$mappings[$eventType])) {
            return self::$mappings[$eventType];
        }

        // Try wildcard matching (e.g. billing.* -> Financial)
        foreach (self::$mappings as $pattern => $category) {
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '*');
                if (str_starts_with($eventType, $prefix)) {
                    return $category;
                }
            }
        }

        return EventCategory::System;
    }
}
