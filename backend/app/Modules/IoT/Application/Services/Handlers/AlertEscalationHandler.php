<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Entities\DeviceAlert;

class AlertEscalationHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Escalate critical unresolved alerts that are older than 2 hours to super admins
        $alerts = DeviceAlert::whereNull('resolved_at')
            ->where('severity', 'critical')
            ->where('raised_at', '<', now()->subHours(2))
            ->get();

        foreach ($alerts as $alert) {
            // Logs notification routing (SMS/Email)
            logger()->warning("ESCALATION: Unresolved critical alert {$alert->alert_type} for device {$alert->device_id}");
        }
    }
}
