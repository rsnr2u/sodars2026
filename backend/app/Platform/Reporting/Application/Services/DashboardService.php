<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Application\Services;

use App\Platform\Reporting\Domain\Entities\Dashboard;
use App\Platform\Reporting\Domain\Entities\DashboardWidget;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Reporting\Infrastructure\Registry\ReportingRegistry;

class DashboardService
{
    public function __construct(
        protected ReportingRegistry $registry
    ) {}

    /**
     * Retrieve visual widget data payloads for a dashboard.
     */
    public function renderWidgets(Dashboard $dashboard): array
    {
        $rendered = [];

        foreach ($dashboard->widgets as $widget) {
            try {
                $report = $this->registry->resolveReport($widget->report_key);
                $params = ReportParameters::fromArray($widget->query_parameters ?? []);
                
                $reportData = $report->generate($params);

                $providerType = str_contains($widget->widget_type, 'chart') ? 'chart' : 'value_card';
                $provider = $this->registry->resolveWidgetProvider($providerType);

                $renderedWidget = $provider->render($widget, $reportData);

                $rendered[] = array_merge($renderedWidget, [
                    'id' => $widget->id,
                    'dimensions' => $widget->dimensions,
                    'drilldown_route' => $widget->drilldown_route,
                ]);
            } catch (\Exception $e) {
                $rendered[] = [
                    'id' => $widget->id,
                    'title' => $widget->title,
                    'value' => 'Error loading widget',
                    'subtitle' => $e->getMessage(),
                    'type' => 'error',
                    'dimensions' => $widget->dimensions,
                ];
            }
        }

        return $rendered;
    }
}
