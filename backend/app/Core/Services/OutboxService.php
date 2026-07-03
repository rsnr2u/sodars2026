<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Context\TraceContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OutboxService
{
    /**
     * Record a new event to the transactional outbox.
     */
    public function record(
        string $aggregateType,
        string $aggregateId,
        string $eventName,
        array $data,
        int $eventVersion = 1,
        string $schemaVersion = '1.0.0',
        ?array $headers = null
    ): void {
        $id = (string) Str::uuid();
        $payload = [
            'id' => $id,
            'type' => $eventName,
            'source' => $aggregateType,
            'subject' => "{$aggregateType}/{$aggregateId}",
            'time' => now()->toIso8601String(),
            'specversion' => '1.0',
            'datacontenttype' => 'application/json',
            'data' => $data,
        ];

        DB::table('outbox_events')->insert([
            'id' => $id,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'event_name' => $eventName,
            'event_version' => $eventVersion,
            'schema_version' => $schemaVersion,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'headers' => json_encode($headers ?? [], JSON_THROW_ON_ERROR),
            'correlation_id' => TraceContext::correlationId(),
            'causation_id' => TraceContext::causationId() ?? $id,
            'trace_id' => TraceContext::traceId(),
            'status' => 'pending',
            'attempts' => 0,
            'available_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reserve pending outbox records atomically using FOR UPDATE SKIP LOCKED.
     * Falls back to simple locking on drivers that don't support SKIP LOCKED (e.g., SQLite).
     */
    public function reserve(int $batchSize = 100): Collection
    {
        return DB::transaction(function () use ($batchSize) {
            $query = DB::table('outbox_events')
                ->whereIn('status', ['pending', 'failed'])
                ->where('available_at', '<=', now())
                ->orderBy('created_at', 'asc')
                ->limit($batchSize);

            // SKIP LOCKED is supported on MySQL 8+ and PostgreSQL, not on SQLite
            $driver = DB::connection()->getDriverName();
            if (in_array($driver, ['mysql', 'pgsql', 'mariadb'], true)) {
                $events = $query->lockForUpdate()->get();
            } else {
                $events = $query->get();
            }

            if ($events->isNotEmpty()) {
                $ids = $events->pluck('id')->toArray();
                DB::table('outbox_events')
                    ->whereIn('id', $ids)
                    ->update([
                        'status' => 'reserved',
                        'attempts' => DB::raw('attempts + 1'),
                        'updated_at' => now(),
                    ]);
            }

            return $events;
        });
    }

    /**
     * Mark an outbox record as processed.
     */
    public function markProcessed(string $id): void
    {
        DB::table('outbox_events')
            ->where('id', $id)
            ->update([
                'status' => 'processed',
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Mark an outbox record as failed, scheduling a retry or promoting to dead letter.
     */
    public function markFailed(string $id, string $errorMessage): void
    {
        $event = DB::table('outbox_events')->where('id', $id)->first();

        if (!$event) {
            return;
        }

        $maxTries = (int) config('foundation.outbox.retry_limit', 5);

        if ($event->attempts >= $maxTries) {
            DB::table('outbox_events')
                ->where('id', $id)
                ->update([
                    'status' => 'dead_letter',
                    'error_message' => $errorMessage,
                    'updated_at' => now(),
                ]);
        } else {
            // Exponential backoff retry mapping: tries * 10 seconds
            $backoffSeconds = $event->attempts * 10;
            DB::table('outbox_events')
                ->where('id', $id)
                ->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'available_at' => now()->addSeconds($backoffSeconds),
                    'updated_at' => now(),
                ]);
        }
    }
}
