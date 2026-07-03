<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Services\OutboxService;
use Illuminate\Support\Facades\DB;
use Tests\Core\FeatureTestCase;

class OutboxServiceTest extends FeatureTestCase
{
    protected OutboxService $outboxService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outboxService = app(OutboxService::class);
    }

    /**
     * Test recording a new outbox event.
     */
    public function test_record_creates_outbox_event(): void
    {
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: 'branch-uuid-001',
            eventName: 'branch.created.v1',
            data: ['name' => 'Test Branch', 'city' => 'Riyadh'],
        );

        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Branch',
            'aggregate_id' => 'branch-uuid-001',
            'event_name' => 'branch.created.v1',
            'status' => 'pending',
            'attempts' => 0,
        ]);
    }

    /**
     * Test CloudEvents payload structure.
     */
    public function test_record_stores_cloud_events_payload(): void
    {
        $this->outboxService->record(
            aggregateType: 'Provider',
            aggregateId: 'provider-uuid-001',
            eventName: 'provider.approved.v1',
            data: ['status' => 'approved'],
        );

        $event = DB::table('outbox_events')
            ->where('event_name', 'provider.approved.v1')
            ->first();

        $payload = json_decode($event->payload, true);

        $this->assertEquals('provider.approved.v1', $payload['type']);
        $this->assertEquals('Provider', $payload['source']);
        $this->assertEquals('Provider/provider-uuid-001', $payload['subject']);
        $this->assertEquals('1.0', $payload['specversion']);
        $this->assertEquals('application/json', $payload['datacontenttype']);
        $this->assertArrayHasKey('data', $payload);
        $this->assertEquals('approved', $payload['data']['status']);
    }

    /**
     * Test reserving pending events.
     */
    public function test_reserve_returns_pending_events(): void
    {
        // Create 3 events
        for ($i = 1; $i <= 3; $i++) {
            $this->outboxService->record(
                aggregateType: 'Booking',
                aggregateId: "booking-{$i}",
                eventName: 'booking.created.v1',
                data: ['number' => $i],
            );
        }

        $reserved = $this->outboxService->reserve(batchSize: 2);

        $this->assertCount(2, $reserved);

        // Verify the reserved events have updated status
        foreach ($reserved as $event) {
            $updated = DB::table('outbox_events')->where('id', $event->id)->first();
            $this->assertEquals('reserved', $updated->status);
            $this->assertEquals(1, $updated->attempts);
        }
    }

    /**
     * Test marking an event as processed.
     */
    public function test_mark_processed_updates_status(): void
    {
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: 'branch-uuid-002',
            eventName: 'branch.updated.v1',
            data: ['name' => 'Updated Branch'],
        );

        $event = DB::table('outbox_events')
            ->where('event_name', 'branch.updated.v1')
            ->first();

        $this->outboxService->markProcessed($event->id);

        $updated = DB::table('outbox_events')->where('id', $event->id)->first();
        $this->assertEquals('processed', $updated->status);
        $this->assertNotNull($updated->processed_at);
    }

    /**
     * Test marking an event as failed with retry backoff.
     */
    public function test_mark_failed_schedules_retry(): void
    {
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: 'branch-uuid-003',
            eventName: 'branch.deleted.v1',
            data: [],
        );

        // Reserve to increment attempts
        $events = $this->outboxService->reserve(1);
        $event = $events->first();

        $this->outboxService->markFailed($event->id, 'Temporary failure');

        $updated = DB::table('outbox_events')->where('id', $event->id)->first();
        $this->assertEquals('failed', $updated->status);
        $this->assertEquals('Temporary failure', $updated->error_message);
    }

    /**
     * Test that events are promoted to dead_letter after exceeding retry limit.
     */
    public function test_mark_failed_promotes_to_dead_letter(): void
    {
        $this->outboxService->record(
            aggregateType: 'Branch',
            aggregateId: 'branch-uuid-004',
            eventName: 'branch.failed.v1',
            data: [],
        );

        $event = DB::table('outbox_events')
            ->where('event_name', 'branch.failed.v1')
            ->first();

        // Set attempts to exceed the retry limit
        $retryLimit = (int) config('foundation.outbox.retry_limit', 5);
        DB::table('outbox_events')
            ->where('id', $event->id)
            ->update(['attempts' => $retryLimit]);

        $this->outboxService->markFailed($event->id, 'Permanent failure');

        $updated = DB::table('outbox_events')->where('id', $event->id)->first();
        $this->assertEquals('dead_letter', $updated->status);
        $this->assertEquals('Permanent failure', $updated->error_message);
    }

    /**
     * Test that event version and schema version are stored.
     */
    public function test_record_stores_versioning_metadata(): void
    {
        $this->outboxService->record(
            aggregateType: 'Inventory',
            aggregateId: 'item-uuid-001',
            eventName: 'inventory.quantity_changed.v2',
            data: ['quantity' => 10],
            eventVersion: 2,
            schemaVersion: '2.0.0',
        );

        $this->assertDatabaseHas('outbox_events', [
            'event_name' => 'inventory.quantity_changed.v2',
            'event_version' => 2,
            'schema_version' => '2.0.0',
        ]);
    }

    /**
     * Test that trace context IDs are captured in outbox records.
     */
    public function test_record_captures_trace_context(): void
    {
        $this->outboxService->record(
            aggregateType: 'Customer',
            aggregateId: 'customer-uuid-001',
            eventName: 'customer.registered.v1',
            data: ['email' => 'test@example.com'],
        );

        $event = DB::table('outbox_events')
            ->where('event_name', 'customer.registered.v1')
            ->first();

        $this->assertNotNull($event->correlation_id);
        $this->assertNotNull($event->trace_id);
    }
}
