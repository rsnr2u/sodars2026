<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Contracts;

use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;

interface Report
{
    /**
     * Get unique report registration key.
     */
    public static function getKey(): string;

    /**
     * Get parameter definitions schema.
     */
    public static function getParameterSchema(): array;

    /**
     * Execute queries and compile structured payload.
     */
    public function generate(ReportParameters $parameters): array;
}
