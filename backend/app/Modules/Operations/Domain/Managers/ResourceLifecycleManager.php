<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Managers;

use App\Modules\Operations\Domain\Entities\OperationalResource;
use App\Modules\Operations\Domain\Entities\ResourceStateHistory;
use App\Modules\Operations\Domain\Entities\ResourceAvailabilityProjection;
use App\Modules\Operations\Domain\Entities\ResourceWorkloadProjection;
use App\Modules\Operations\Domain\Enums\ResourceState;
use App\Modules\Operations\Domain\Events\ResourceAssigned;
use App\Modules\Operations\Domain\Events\ResourceReleased;
use App\Modules\Operations\Domain\Events\CalendarUpdated; // standard events mapping
use Illuminate\Support\Str;

class ResourceLifecycleManager
{
    public function create(array $data): OperationalResource
    {
        $resource = OperationalResource::create(array_merge($data, [
            'id' => (string) Str::uuid(),
            'status' => $data['status'] ?? 'active',
        ]));

        // Initialize state history and projections
        ResourceStateHistory::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $resource->organization_id,
            'resource_id' => $resource->id,
            'state' => ResourceState::Available,
            'started_at' => now(),
            'reason' => 'Resource initialized.',
        ]);

        ResourceAvailabilityProjection::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $resource->organization_id,
            'resource_id' => $resource->id,
            'current_state' => ResourceState::Available,
            'blocked_time_slots' => [],
            'last_updated_at' => now(),
        ]);

        ResourceWorkloadProjection::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $resource->organization_id,
            'resource_id' => $resource->id,
            'assigned_schedules_count' => 0,
            'total_allocated_seconds' => 0,
            'utilization_score' => 0,
        ]);

        return $resource;
    }

    public function recordStateChange(OperationalResource $resource, ResourceState $state, ?string $reason = null): void
    {
        // 1. Close current active state history record
        ResourceStateHistory::where('resource_id', $resource->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        // 2. Insert new state record
        ResourceStateHistory::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $resource->organization_id,
            'resource_id' => $resource->id,
            'state' => $state,
            'started_at' => now(),
            'reason' => $reason,
        ]);

        // Dispatch events based on state changes
        if ($state === ResourceState::Assigned) {
            event(new ResourceAssigned($resource->id, 1, $resource->toArray()));
        } elseif ($state === ResourceState::Available) {
            event(new ResourceReleased($resource->id, 1, $resource->toArray()));
        }
    }
}
