<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Providers;

use App\Platform\Reporting\Domain\Contracts\WidgetProvider;
use App\Platform\Reporting\Domain\Entities\DashboardWidget;

class ChartProvider implements WidgetProvider
{
    public function getWidgetType(): string
    {
        return 'chart';
    }

    public function render(DashboardWidget $widget, array $reportData): array
    {
        $records = $reportData['records'] ?? [];
        $labels = [];
        $values = [];

        if ($widget->report_key === 'trial_balance') {
            $grouped = collect($records)->groupBy('entry_type');
            foreach ($grouped as $type => $group) {
                $labels[] = ucfirst((string) $type);
                $values[] = $group->sum('amount_cents') / 100;
            }
        } elseif ($widget->report_key === 'inventory_occupancy') {
            $grouped = collect($records)->groupBy('availability_status');
            foreach ($grouped as $status => $group) {
                $labels[] = ucfirst((string) $status);
                $values[] = $group->count();
            }
        } elseif ($widget->report_key === 'booking_performance') {
            $grouped = collect($records)->groupBy('status');
            foreach ($grouped as $status => $group) {
                $labels[] = ucfirst((string) $status);
                $values[] = $group->count();
            }
        }

        return [
            'title' => $widget->title,
            'widget_type' => $widget->widget_type,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $widget->title,
                        'data' => $values,
                    ]
                ]
            ],
            'type' => 'chart',
        ];
    }
}
