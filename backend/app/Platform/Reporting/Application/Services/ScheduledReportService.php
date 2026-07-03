<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Application\Services;

use App\Platform\Reporting\Domain\Entities\ScheduledReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ScheduledReportService
{
    /**
     * Get all active scheduled reports.
     *
     * @return Collection<int, ScheduledReport>
     */
    public function getActive(): Collection
    {
        return ScheduledReport::where('is_active', true)->get();
    }

    /**
     * Create a scheduled report entry.
     */
    public function create(
        string $userId,
        string $reportKey,
        string $name,
        string $cronExpression,
        array $queryParameters,
        array $recipientEmails,
        string $exportFormat = 'csv'
    ): ScheduledReport {
        return ScheduledReport::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'report_key' => $reportKey,
            'name' => $name,
            'cron_expression' => $cronExpression,
            'query_parameters' => $queryParameters,
            'recipient_emails' => $recipientEmails,
            'export_format' => $exportFormat,
            'is_active' => true,
        ]);
    }
}
