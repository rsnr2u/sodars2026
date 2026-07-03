<?php

declare(strict_types=1);

namespace App\Core\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportService
{
    /**
     * Export data into streamed CSV format.
     */
    public function exportCsv(array $headers, array $data, string $filename = 'export.csv'): StreamedResponse
    {
        $responseHeaders = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(static function () use ($headers, $data): void {
            $file = fopen('php://output', 'w');
            if ($file !== false) {
                fputcsv($file, $headers);
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            }
        }, 200, $responseHeaders);
    }
}
