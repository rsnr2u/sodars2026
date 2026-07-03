<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Core\ApiTestCase;

class BranchOutboxTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test outbox events are recorded with context identifiers.
     */
    public function test_outbox_event_recorded_on_creation(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/v1/admin/branches', [
            'name' => 'Branch India North',
            'code' => 'IN-NORTH',
            'support_email' => 'north.support@sodars.com',
            'support_phone' => '+911145678901',
        ]);

        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Branch',
            'event_name' => 'branch.created.v1',
            'status' => 'pending',
        ]);

        $event = DB::table('outbox_events')
            ->where('event_name', 'branch.created.v1')
            ->first();

        $this->assertNotNull($event->correlation_id);
        $this->assertNotNull($event->trace_id);

        $payload = json_decode($event->payload, true);
        $this->assertEquals('branch.created.v1', $payload['type']);
        $this->assertEquals('Branch India North', $payload['data']['name']);
    }
}
