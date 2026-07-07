<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\Transport\Domain\Entities\Route as TransportRoute;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Identity\Application\Services\IdentityContext;

class RouteAnalysisReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'route_analysis';
    }

    public static function getParameterSchema(): array
    {
        return [
            'status' => 'nullable|string',
        ];
    }

    public function generate(ReportParameters $parameters): array
    {
        $status = $parameters->getString('status');
        $query = TransportRoute::query();

        // Multi-tenant scope
        $orgId = IdentityContext::organizationId();
        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $records = $query->with(['vehicle', 'driver'])->orderBy('created_at', 'desc')->get();

        return [
            'summary' => [
                'total_routes' => $records->count(),
                'completed_routes' => $records->where('status', RouteStatus::Completed)->count(),
                'cancelled_routes' => $records->where('status', RouteStatus::Cancelled)->count(),
                'total_planned_distance' => (float) $records->sum('planned_distance_km'),
                'total_actual_distance' => (float) $records->sum('actual_distance_km'),
            ],
            'records' => $records->map(fn(TransportRoute $r) => [
                'id' => $r->id,
                'route_number' => $r->route_number,
                'vehicle_number' => $r->vehicle?->vehicle_number,
                'driver_name' => $r->driver ? ($r->driver->first_name . ' ' . $r->driver->last_name) : null,
                'start_location' => $r->start_location,
                'end_location' => $r->end_location,
                'planned_distance_km' => $r->planned_distance_km,
                'planned_duration_minutes' => $r->planned_duration_minutes,
                'actual_distance_km' => $r->actual_distance_km,
                'actual_duration_minutes' => $r->actual_duration_minutes,
                'status' => $r->status->value ?? $r->status,
                'started_at' => $r->started_at?->toIso8601String(),
                'completed_at' => $r->completed_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['ID', 'Route Number', 'Vehicle Number', 'Driver Name', 'Start', 'End', 'Planned Dist (km)', 'Planned Dur (min)', 'Actual Dist (km)', 'Actual Dur (min)', 'Status', 'Started At', 'Completed At'];
    }

    public function getExportRows(array $data): array
    {
        return array_map(fn($r) => [
            $r['id'],
            $r['route_number'],
            $r['vehicle_number'],
            $r['driver_name'],
            $r['start_location'],
            $r['end_location'],
            $r['planned_distance_km'],
            $r['planned_duration_minutes'],
            $r['actual_distance_km'],
            $r['actual_duration_minutes'],
            $r['status'],
            $r['started_at'],
            $r['completed_at'],
        ], $data['records'] ?? []);
    }
}
