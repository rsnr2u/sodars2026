<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Pipelines;

use App\Modules\Finance\Domain\Entities\Invoice;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class GenerateInvoicePipeline
{
    public function __construct(protected Pipeline $pipeline) {}

    public function execute(array $passable): Invoice
    {
        return DB::transaction(function () use ($passable) {
            $result = $this->pipeline
                ->send($passable)
                ->through([
                    Stages\ValidateBookingStatus::class,
                    Stages\CalculateInvoiceTotals::class,
                    Stages\GenerateBookingSnapshot::class,
                    Stages\PersistInvoice::class,
                    Stages\CreateRevenueRecognitionSchedule::class,
                    Stages\PublishInvoiceEvents::class,
                ])
                ->then(function (array $passable) {
                    return $passable['invoice'];
                });

            return $result;
        });
    }
}
