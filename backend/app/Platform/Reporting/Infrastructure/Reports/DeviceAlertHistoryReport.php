<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\DeviceAlert;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class DeviceAlertHistoryReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'device_alert_history';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $alerts = DeviceAlert::with('device')->get();

        return [
            'records' => $alerts->map(fn(DeviceAlert $a) => [
                'id' => $a->id,
                'device_number' => $a->device?->device_number ?? 'Unknown',
                'alert_type' => $a->alert_type,
                'severity' => $a->severity,
                'message' => $a->message,
                'raised_at' => $a->raised_at?->toIso8601String(),
                'resolved_at' => $a->resolved_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Alert ID', 'Device Number', 'Alert Type', 'Severity', 'Message', 'Raised At', 'Resolved At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['id'], $r['device_number'], $r['alert_type'], $r['severity'], $r['message'], $r['raised_at'], $r['resolved_at']];
        }
        return $rows;
    }
}
