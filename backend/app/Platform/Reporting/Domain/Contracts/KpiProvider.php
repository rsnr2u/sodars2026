<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Contracts;

interface KpiProvider
{
    /**
     * Get unique KPI key (e.g. 'today_revenue').
     */
    public static function getKey(): string;

    /**
     * Fetch KPI metric metadata (title, value, subtitle, trend).
     */
    public function getValue(): array;
}
