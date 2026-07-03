<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Application\Jobs;

use App\Platform\Reporting\Application\Services\ReportExportService;
use App\Platform\Reporting\Domain\Entities\ScheduledReport;
use App\Platform\Reporting\Domain\Entities\ReportExecution;
use App\Platform\Notifications\Application\Services\NotificationService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class RunScheduledReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $scheduledReportId
    ) {}

    public function handle(
        ReportExportService $exportService,
        NotificationService $notificationService,
        \App\Platform\DAM\Application\Services\DAMService $damService
    ): void {
        $scheduled = ScheduledReport::findOrFail($this->scheduledReportId);

        $execution = ReportExecution::create([
            'id' => (string) Str::uuid(),
            'scheduled_report_id' => $scheduled->id,
            'report_key' => $scheduled->report_key,
            'status' => 'running',
            'started_at' => now(),
            'executed_by' => $scheduled->user_id,
            'context_snapshot' => $scheduled->query_parameters ?? [],
        ]);

        $startTime = microtime(true);

        try {
            $asset = $exportService->exportToDam(
                $scheduled->report_key,
                $scheduled->query_parameters ?? [],
                $scheduled->user_id
            );

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            $execution->update([
                'status' => 'completed',
                'completed_at' => now(),
                'duration_ms' => $duration,
                'dam_asset_id' => $asset->id,
            ]);

            $scheduled->update(['last_run_at' => now()]);

            $emails = (array) ($scheduled->recipient_emails ?? []);
            $users = User::whereIn('email', $emails)->get();

            if ($users->isEmpty()) {
                $creator = User::find($scheduled->user_id);
                if ($creator) {
                    $users->push($creator);
                }
            }

            foreach ($users as $user) {
                $notificationService->send(
                    (string) $user->id,
                    'scheduled_report_ready',
                    [
                        'report_name' => $scheduled->name,
                        'download_url' => $damService->getUrl($asset->currentVersion->file->storage_path ?? ''),
                    ]
                );
            }
        } catch (\Exception $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            
            $execution->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_ms' => $duration,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
