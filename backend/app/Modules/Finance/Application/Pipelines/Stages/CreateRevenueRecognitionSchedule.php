<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines\Stages;

use App\Modules\Finance\Domain\Services\RevenueRecognition\RevenueRecognitionEngine;
use Closure;

class CreateRevenueRecognitionSchedule
{
    public function __construct(protected RevenueRecognitionEngine $engine) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];
        $invoiceType = $passable['invoice_type'];

        // We only generate recognized revenue plans for Tax Invoices
        if ($invoiceType === 'tax_invoice') {
            $this->engine->generateSchedules($booking);
        }

        return $next($passable);
    }
}
