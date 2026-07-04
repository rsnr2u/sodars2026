<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Providers;

use App\Platform\Reporting\Infrastructure\Registry\ReportingRegistry;
use App\Platform\Reporting\Infrastructure\Drivers\CsvExportDriver;
use App\Platform\Reporting\Infrastructure\Reports\TrialBalanceReport;
use App\Platform\Reporting\Infrastructure\Reports\InventoryOccupancyReport;
use App\Platform\Reporting\Infrastructure\Reports\BookingPerformanceReport;
use App\Platform\Reporting\Infrastructure\Reports\LeadSourceReport;
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
