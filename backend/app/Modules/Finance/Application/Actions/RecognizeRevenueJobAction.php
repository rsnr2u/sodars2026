<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Services\RevenueRecognition\RevenueRecognitionEngine;
use App\Modules\Finance\Domain\Events\RevenueRecognized;
use App\Modules\Finance\Domain\Entities\RevenueRecognitionEntry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class RecognizeRevenueJobAction
{
    public function __construct(
        protected RevenueRecognitionEngine $engine,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $asOfDate): array
    {
        $entries = $this->engine->recognizePending($asOfDate);

        foreach ($entries as $entry) {
            $eventData = [
                'entry_id' => $entry->id,
                'schedule_id' => $entry->schedule_id,
                'recognition_date' => $entry->recognition_date->toDateString(),
                'amount_cents' => $entry->amount_cents,
                'status' => 'recognized',
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'RevenueRecognitionEntry',
                aggregateId: $entry->id,
                eventName: 'revenue.recognized.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Event
            Event::dispatch(new RevenueRecognized(
                aggregateId: $entry->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));
        }

        return $entries;
    }
}
