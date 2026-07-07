<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

class DeviceCommandReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'device_command';
    }

    public static function getParameterSchema(): array
    {
        return [];
    }

    public function generate(ReportParameters $parameters): array
    {
        $commands = DeviceCommand::with('device')->get();

        return [
            'records' => $commands->map(fn(DeviceCommand $c) => [
                'command_uuid' => $c->command_uuid,
                'device_number' => $c->device?->device_number ?? 'Unknown',
                'command_type' => $c->command_type,
                'status' => $c->status->value,
                'attempts' => $c->attempts,
                'completed_at' => $c->completed_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Command UUID', 'Device Number', 'Command Type', 'Status', 'Attempts', 'Completed At'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $r) {
            $rows[] = [$r['command_uuid'], $r['device_number'], $r['command_type'], $r['status'], $r['attempts'], $r['completed_at']];
        }
        return $rows;
    }
}
