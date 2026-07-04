<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\CRM\Domain\Entities\Account;
use App\Modules\CRM\Domain\Entities\Contact;
use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Entities\Opportunity;
use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Domain\Entities\PipelineStage;
use App\Modules\CRM\Application\Services\LeadLifecycleService;
use App\Modules\CRM\Application\Services\OpportunityLifecycleService;
use App\Modules\CRM\Application\Services\QuotationLifecycleService;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use App\Platform\Reporting\Infrastructure\Reports\LeadSourceReport;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Core\Context\ContextManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class CrmIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Organization $org1;
    protected Organization $org2;
    protected PipelineStage $pipelineStage;

    protected function setUp(): void
    {
        parent::setUp();
        ContextManager::clear();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        // Tenants
        $this->org1 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org One',
            'slug' => 'org-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Org Two',
            'slug' => 'org-two',
            'is_active' => true,
        ]);

        // Memberships
        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'user_id' => $this->user1->id,
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user1->update(['organization_id' => $this->org1->id]);

        OrganizationMember::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org2->id,
            'user_id' => $this->user2->id,
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $this->user2->update(['organization_id' => $this->org2->id]);

        // Pipeline Stage
        $this->pipelineStage = PipelineStage::create([
            'id' => (string) Str::uuid(),
            'name' => 'Qualification',
            'display_order' => 1,
            'probability' => 10,
            'is_closed' => false,
            'is_won' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 1. Multi-Tenant Row Level Isolation (RLS)
    // ─────────────────────────────────────────────────────

    public function test_crm_tenant_isolation_rls(): void
    {
        // Save CRM records under Org 1 context
        $this->actingAs($this->user1);
        ContextManager::boot();

        $account1 = Account::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Corp Org1',
            'industry' => 'Technology',
        ]);

        $contact1 = Contact::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account1->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@acme.com',
            'phone' => '+919999999999',
        ]);

        $lead1 = Lead::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account1->id,
            'contact_id' => $contact1->id,
            'title' => 'Acme Corp CRM License Deal',
            'source' => 'website',
            'status' => 'new',
            'lead_score' => 50,
        ]);

        // Switch to Org 2 context
        $this->actingAs($this->user2);
        ContextManager::boot();

        $account2 = Account::create([
            'id' => (string) Str::uuid(),
            'name' => 'Global Inc Org2',
            'industry' => 'Finance',
        ]);

        $contact2 = Contact::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account2->id,
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@global.com',
            'phone' => '+918888888888',
        ]);

        $lead2 = Lead::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account2->id,
            'contact_id' => $contact2->id,
            'title' => 'Global Inc Campaign Deal',
            'source' => 'referral',
            'status' => 'new',
            'lead_score' => 40,
        ]);

        // Check Org 1 query results
        $this->actingAs($this->user1);
        ContextManager::boot();

        $this->assertCount(1, Lead::all());
        $this->assertEquals('Acme Corp CRM License Deal', Lead::first()->title);
        $this->assertEquals($this->org1->id, Lead::first()->organization_id);

        $this->assertCount(1, Account::all());
        $this->assertEquals('Acme Corp Org1', Account::first()->name);

        // Check Org 2 query results
        $this->actingAs($this->user2);
        ContextManager::boot();

        $this->assertCount(1, Lead::all());
        $this->assertEquals('Global Inc Campaign Deal', Lead::first()->title);
        $this->assertEquals($this->org2->id, Lead::first()->organization_id);

        $this->assertCount(1, Account::all());
        $this->assertEquals('Global Inc Org2', Account::first()->name);
    }

    // ─────────────────────────────────────────────────────
    // 2. CRM Lifecycle, Audit Trail, & Outbox Logging
    // ─────────────────────────────────────────────────────

    public function test_crm_lifecycle_audit_and_outbox(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $account = Account::create([
            'id' => (string) Str::uuid(),
            'name' => 'Audit Test Corp',
        ]);

        $lead = Lead::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'title' => 'Audit Test Lead',
            'source' => 'phone',
            'status' => 'new',
        ]);

        $leadService = app(LeadLifecycleService::class);

        // 1. Creation Audit
        $leadService->recordCreation($lead);

        // Assert outbox created
        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Lead',
            'aggregate_id' => $lead->id,
            'event_name' => 'crm.lead.created.v1',
        ]);

        // Assert audit log created
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'crm.lead.created',
            'organization_id' => $this->org1->id,
        ]);

        // 2. Status Changed Audit
        $leadService->recordStatusChange($lead, 'new', 'contacted');

        // Assert status changed outbox event
        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Lead',
            'aggregate_id' => $lead->id,
            'event_name' => 'crm.lead.status_changed.v1',
        ]);

        // Assert status changed audit event
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'crm.lead.status_changed',
            'organization_id' => $this->org1->id,
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 3. Search Reindexing Job Dispatch
    // ─────────────────────────────────────────────────────

    public function test_crm_reindexing_job_dispatch(): void
    {
        Queue::fake();

        $this->actingAs($this->user1);
        ContextManager::boot();

        $account = Account::create([
            'id' => (string) Str::uuid(),
            'name' => 'Search Index Corp',
        ]);

        $lead = Lead::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'title' => 'Search Index Lead',
            'source' => 'phone',
            'status' => 'new',
        ]);

        // Fire LeadCreated event to test subscriber
        event(new \App\Modules\CRM\Domain\Events\LeadCreated(
            aggregateId: $lead->id,
            aggregateVersion: 1,
            data: $lead->toArray(),
            occurredAt: now()->toIso8601String(),
            correlationId: (string) Str::uuid(),
            traceId: (string) Str::uuid()
        ));

        // Assert search index job is dispatched
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($lead) {
            return $job->entityId === $lead->id;
        });
    }

    // ─────────────────────────────────────────────────────
    // 4. Tenant-Safe Reports (LeadSourceReport)
    // ─────────────────────────────────────────────────────

    public function test_crm_lead_source_report_scoping(): void
    {
        // Create lead under Org 1
        $this->actingAs($this->user1);
        ContextManager::boot();

        $lead1 = Lead::create([
            'id' => (string) Str::uuid(),
            'title' => 'Org 1 Website Lead',
            'source' => 'website',
            'status' => 'new',
        ]);

        // Create lead under Org 2
        $this->actingAs($this->user2);
        ContextManager::boot();

        $lead2 = Lead::create([
            'id' => (string) Str::uuid(),
            'title' => 'Org 2 Website Lead',
            'source' => 'website',
            'status' => 'new',
        ]);

        // Query report under Org 1. Should NOT see Org 2's lead
        $this->actingAs($this->user1);
        ContextManager::boot();

        $report = new LeadSourceReport();
        $params = ReportParameters::fromArray(['source' => 'website']);
        $result = $report->generate($params);

        $this->assertEquals(1, $result['summary']['total_leads']);
        $this->assertCount(1, $result['records']);
        $this->assertEquals('Org 1 Website Lead', $result['records'][0]['title']);
    }
}
