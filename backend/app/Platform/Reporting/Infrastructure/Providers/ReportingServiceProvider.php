<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Providers;

use App\Platform\Reporting\Infrastructure\Registry\ReportingRegistry;
use App\Platform\Reporting\Infrastructure\Drivers\CsvExportDriver;
use App\Platform\Reporting\Infrastructure\Reports\TrialBalanceReport;
use App\Platform\Reporting\Infrastructure\Reports\InventoryOccupancyReport;
use App\Platform\Reporting\Infrastructure\Reports\BookingPerformanceReport;
use App\Platform\Reporting\Infrastructure\Reports\LeadSourceReport;
use App\Platform\Reporting\Infrastructure\Reports\RevenueReport;
use App\Platform\Reporting\Infrastructure\Reports\ReceivablesReport;
use App\Platform\Reporting\Infrastructure\Reports\OutstandingInvoicesReport;
use App\Platform\Reporting\Infrastructure\Reports\ProviderSettlementReport;
use App\Platform\Reporting\Infrastructure\Reports\ProviderPerformanceReport;
use App\Platform\Reporting\Infrastructure\Reports\ProviderActivityReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignPerformanceReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignTimelineReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignUtilizationReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignActivityReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignOccupancyReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignSettlementReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignRevenueReport;
use App\Platform\Reporting\Infrastructure\Reports\CampaignBudgetVarianceReport;
use App\Platform\Reporting\Infrastructure\Providers\ValueCardProvider;
use App\Platform\Reporting\Infrastructure\Providers\ChartProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReportingRegistry::class, function () {
            $registry = new ReportingRegistry();

            // Register Reports
            $registry->registerReport(TrialBalanceReport::class);
            $registry->registerReport(InventoryOccupancyReport::class);
            $registry->registerReport(BookingPerformanceReport::class);
            $registry->registerReport(LeadSourceReport::class);
            $registry->registerReport(RevenueReport::class);
            $registry->registerReport(ReceivablesReport::class);
            $registry->registerReport(OutstandingInvoicesReport::class);
            $registry->registerReport(ProviderSettlementReport::class);
            $registry->registerReport(ProviderPerformanceReport::class);
            $registry->registerReport(ProviderActivityReport::class);
            $registry->registerReport(CampaignPerformanceReport::class);
            $registry->registerReport(CampaignTimelineReport::class);
            $registry->registerReport(CampaignUtilizationReport::class);
            $registry->registerReport(CampaignActivityReport::class);
            $registry->registerReport(CampaignOccupancyReport::class);
            $registry->registerReport(CampaignSettlementReport::class);
            $registry->registerReport(CampaignRevenueReport::class);
            $registry->registerReport(CampaignBudgetVarianceReport::class);

            // Wallet Reports
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WalletStatementReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WalletBalancesReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WalletActivityReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WalletAgingReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WithdrawalHistoryReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\SettlementPayoutReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WalletReconciliationReport::class);

            // Transport Reports
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\FleetUtilizationReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\FleetMaintenanceReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\FuelEfficiencyReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\RouteAnalysisReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\VehicleDowntimeReport::class);

            // IoT Reports
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DeviceInventoryReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DeviceHealthReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DeviceUptimeReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DeviceAvailabilityReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\TelemetryStatisticsReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\FirmwareComplianceReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DeviceCommandReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DeviceAlertHistoryReport::class);

            // Operations Reports
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ScheduleReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\DispatchReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\CapacityPlanningReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\WorkloadReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ResourceUtilizationReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\CalendarReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ConflictReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ScheduleTimelineReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\SLAComplianceReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ResourceIdleTimeReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ShiftCoverageReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\AssignmentAccuracyReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\OptimizationSavingsReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ScheduleVarianceReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\PlannedVsActualReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ETAAccuracyReport::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\RecurringScheduleEffectiveness::class);
            $registry->registerReport(\App\Platform\Reporting\Infrastructure\Reports\ConflictResolutionTimeReport::class);

            // Register Export Drivers
            $registry->registerExportDriver(CsvExportDriver::class);

            // Register Widget Providers
            $registry->registerWidgetProvider(ValueCardProvider::class);
            $registry->registerWidgetProvider(ChartProvider::class);

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerNotificationTemplate();
    }

    protected function registerRoutes(): void
    {
        $routeFile = app_path('Platform/Reporting/Presentation/Routes/api.php');
        if (file_exists($routeFile)) {
            Route::middleware('api')->group($routeFile);
        }
    }

    protected function registerNotificationTemplate(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification_templates')) {
                return;
            }

            $template = \App\Platform\Notifications\Domain\Entities\NotificationTemplate::firstOrCreate(
                ['key' => 'scheduled_report_ready'],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => 'Scheduled Report Ready Notification',
                    'category' => 'transactional',
                    'active_version_number' => 1,
                ]
            );

            \App\Platform\Notifications\Domain\Entities\NotificationTemplateVersion::firstOrCreate(
                ['template_id' => $template->id, 'version_number' => 1],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'subject' => 'Your scheduled report {{report_name}} is ready',
                    'content' => [
                        'email' => [
                            'body' => 'Hello, your scheduled report {{report_name}} has been generated successfully. Download here: {{download_url}}'
                        ],
                        'in_app' => [
                            'title' => 'success',
                            'body' => 'Report {{report_name}} is ready.'
                        ]
                    ],
                    'is_active' => true,
                ]
            );
        } catch (\Exception $e) {
            // Fail silently
        }
    }
}
