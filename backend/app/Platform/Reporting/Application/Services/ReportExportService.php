<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Application\Services;

use App\Platform\Reporting\Domain\Contracts\Report;
use App\Platform\Reporting\Domain\Contracts\Exportable;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Reporting\Infrastructure\Registry\ReportingRegistry;
use App\Platform\DAM\Application\Services\DAMService;
use App\Platform\DAM\Domain\Entities\Asset;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class ReportExportService
{
    public function __construct(
        protected ReportingRegistry $registry,
        protected DAMService $damService
    ) {}

    /**
     * Run a report, compile the format, upload it to the DAM system, and return the logical Asset.
     */
    public function exportToDam(string $reportKey, array $parameters, string $userId): Asset
    {
        $report = $this->registry->resolveReport($reportKey);
        if (!$report instanceof Exportable) {
            throw new InvalidArgumentException("Report '{$reportKey}' does not support exports.");
        }

        $params = ReportParameters::fromArray($parameters);
        $data = $report->generate($params);

        $headers = $report->getExportHeaders();
        $rows = $report->getExportRows($data);

        $driver = $this->registry->resolveExportDriver('csv');
        $csvContent = $driver->compile($headers, $rows);

        $tempPath = tempnam(sys_get_temp_dir(), 'rpt_');
        file_put_contents($tempPath, $csvContent);

        $filename = "{$reportKey}_" . date('Ymd_His') . '.csv';

        $uploadedFile = new UploadedFile(
            $tempPath,
            $filename,
            'text/csv',
            null,
            true
        );

        try {
            $asset = $this->damService->upload(
                $uploadedFile,
                "Report: " . ucfirst(str_replace('_', ' ', $reportKey)),
                "Auto-generated execution report run at " . date('Y-m-d H:i:s')
            );
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return $asset;
    }
}
