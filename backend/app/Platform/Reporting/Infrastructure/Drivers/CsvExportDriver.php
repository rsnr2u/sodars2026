<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Infrastructure\Drivers;

use App\Platform\Reporting\Domain\Contracts\ExportDriver;

class CsvExportDriver implements ExportDriver
{
    public function getFormat(): string
    {
        return 'csv';
    }

    public function compile(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        if (!$handle) {
            return '';
        }

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content ?: '';
    }
}
