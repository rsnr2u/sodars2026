<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Reports;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Modules\Providers\Domain\Entities\Provider;

class ProviderPerformanceReport implements Report, Exportable
{
    public static function getKey(): string
    {
        return 'provider_performance';
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

        $query = Provider::query()->with(['activeSubscription']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $providers = $query->take(500)->get();

        $totalActiveScreens = 0;
        foreach ($providers as $provider) {
            if ($provider->activeSubscription) {
                $totalActiveScreens += $provider->activeSubscription->max_active_screens;
            }
        }

        $records = $providers->map(fn(Provider $p) => [
            'id' => $p->id,
            'company_name' => $p->company_name,
            'provider_code' => $p->provider_code,
            'status' => $p->status instanceof \BackedEnum ? $p->status->value : (string) $p->status,
            'max_active_screens' => $p->activeSubscription->max_active_screens ?? 0,
            'billing_cycle' => $p->activeSubscription->billing_cycle->value ?? $p->activeSubscription->billing_cycle ?? 'none',
        ])->toArray();

        return [
            'summary' => [
                'total_providers' => $providers->count(),
                'total_active_screens' => $totalActiveScreens,
            ],
            'records' => $records,
        ];
    }

    public function getExportHeaders(): array
    {
        return ['Company Name', 'Code', 'Status', 'Max Active Screens', 'Billing Cycle'];
    }

    public function getExportRows(array $data): array
    {
        $rows = [];
        foreach ($data['records'] ?? [] as $rec) {
            $rows[] = [
                $rec['company_name'],
                $rec['provider_code'],
                $rec['status'],
                $rec['max_active_screens'],
                $rec['billing_cycle'],
            ];
        }
        return $rows;
    }
}
