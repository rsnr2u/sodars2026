<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Contracts;

interface ExportDriver
{
    /**
     * Get associated format key (e.g. 'csv').
     */
    public function getFormat(): string;

    /**
     * Compile report headers and rows into a string/binary payload.
     *
     * @param array<int, string> $headers
     * @param array<int, array<int, mixed>> $rows
     */
    public function compile(array $headers, array $rows): string;
}
