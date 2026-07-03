<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Contracts;

interface Exportable
{
    /**
     * Define export file headers.
     *
     * @return array<int, string>
     */
    public function getExportHeaders(): array;

    /**
     * Format generated report records into sequential row arrays.
     *
     * @return array<int, array<int, mixed>>
     */
    public function getExportRows(array $data): array;
}
