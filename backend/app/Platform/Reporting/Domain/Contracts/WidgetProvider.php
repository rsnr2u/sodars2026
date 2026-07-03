<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Contracts;

use App\Platform\Reporting\Domain\Entities\DashboardWidget;

interface WidgetProvider
{
    /**
     * Get the widget type key (e.g. 'chart', 'value_card').
     */
    public function getWidgetType(): string;

    /**
     * Compute widget visualization payload from source report metrics.
     */
    public function render(DashboardWidget $widget, array $reportData): array;
}
