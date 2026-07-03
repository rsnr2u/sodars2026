<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Providers;

use App\Platform\Reporting\Domain\Contracts\WidgetProvider;
use App\Platform\Reporting\Domain\Entities\DashboardWidget;
use Illuminate\Support\Arr;

class ValueCardProvider implements WidgetProvider
{
    public function getWidgetType(): string
    {
        return 'value_card';
    }

    public function render(DashboardWidget $widget, array $reportData): array
    {
        $summary = $reportData['summary'] ?? [];

        $value = 'N/A';
        $subtitle = '';

        if ($widget->report_key === 'trial_balance') {
            $value = '$' . number_format(($summary['total_debit_cents'] ?? 0) / 100, 2);
            $subtitle = ($summary['is_balanced'] ?? false) ? 'Balanced ledger' : 'Ledger unbalanced';
        } elseif ($widget->report_key === 'inventory_occupancy') {
            $value = number_format($summary['occupancy_rate_percentage'] ?? 0.0, 2) . '%';
            $subtitle = ($summary['occupied_slots'] ?? 0) . ' of ' . ($summary['total_slots'] ?? 0) . ' slots active';
        } elseif ($widget->report_key === 'booking_performance') {
            $value = (string) ($summary['total_bookings'] ?? 0);
            $subtitle = 'Total revenue: $' . number_format(($summary['total_revenue_cents'] ?? 0) / 100, 2);
        }

        return [
            'title' => $widget->title,
            'value' => $value,
            'subtitle' => $subtitle,
            'type' => 'value_card',
        ];
    }
}
