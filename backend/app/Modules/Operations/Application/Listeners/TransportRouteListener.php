<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Listeners;

use App\Modules\Operations\Domain\Services\OperationsLifecycleService;
use App\Modules\Operations\Domain\Entities\Schedule;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use Illuminate\Support\Facades\Log;

class TransportRouteListener
{
    /**
     * React to transport route dispatch/complete events to sync operations status transitions.
     */
    public function handle(object $event): void
    {
        $classBasename = class_basename($event);
        $routeId = $event->aggregateId;

        // Query schedule matching this route id
        $schedule = Schedule::where('schedule_type', 'route_dispatch')
            ->where(function ($query) use ($routeId) {
                $query->whereJsonContains('metadata->route_id', $routeId);
            })
            ->first();

        if (!$schedule) {
            return;
        }

        $service = app(OperationsLifecycleService::class);

        if ($classBasename === 'RouteDispatched') {
            $service->transitionSchedule($schedule, ScheduleStatus::Dispatched, "Linked transport route [{$routeId}] has been dispatched.");
        } elseif ($classBasename === 'RouteCompleted') {
            $service->transitionSchedule($schedule, ScheduleStatus::Completed, "Linked transport route [{$routeId}] has completed.");
        }
    }
}
