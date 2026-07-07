<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\Payment;
use App\Modules\Finance\Domain\Enums\InvoiceStatus;
use App\Modules\Finance\Application\Services\InvoiceLifecycleService;
use App\Modules\Finance\Application\Services\PaymentLifecycleService;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Domain\Entities\OrganizationMember;
use App\Platform\Audit\Domain\Entities\AuditEvent;
use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use App\Platform\Reporting\Infrastructure\Reports\RevenueReport;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Core\Context\ContextManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class FinanceIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Organization $org1;
    protected Organization $org2;
    protected Branch $branch;

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
            'name' => 'Finance Org One',
            'slug' => 'fin-org-one',
            'is_active' => true,
        ]);

        $this->org2 = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Finance Org Two',
            'slug' => 'fin-org-two',
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

        // Branch for invoices
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Branch',
            'code' => 'TB001',
            'support_email' => 'test@sodars.com',
            'support_phone' => '+918000000000',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 1. Multi-Tenant Row Level Isolation (RLS)
    // ─────────────────────────────────────────────────────

    public function test_finance_tenant_isolation_rls(): void
    {
        // Org 1 creates an invoice
        $this->actingAs($this->user1);
        ContextManager::boot();

        $invoice1 = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-ORG1-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal_cents' => 100000,
            'discount_cents' => 0,
            'tax_cents' => 18000,
            'grand_total_cents' => 118000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Issued->value,
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        // Org 2 creates an invoice
        $this->actingAs($this->user2);
        ContextManager::boot();

        $invoice2 = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-ORG2-001',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal_cents' => 200000,
            'discount_cents' => 0,
            'tax_cents' => 36000,
            'grand_total_cents' => 236000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Issued->value,
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        // Query under Org 1 – should only see its own invoice
        $this->actingAs($this->user1);
        ContextManager::boot();

        $this->assertCount(1, Invoice::all());
        $this->assertEquals('INV-ORG1-001', Invoice::first()->invoice_number);
        $this->assertEquals($this->org1->id, Invoice::first()->organization_id);

        // Query under Org 2 – should only see its own invoice
        $this->actingAs($this->user2);
        ContextManager::boot();

        $this->assertCount(1, Invoice::all());
        $this->assertEquals('INV-ORG2-001', Invoice::first()->invoice_number);
        $this->assertEquals($this->org2->id, Invoice::first()->organization_id);
    }

    // ─────────────────────────────────────────────────────
    // 2. Lifecycle, Audit Trail, & Outbox
    // ─────────────────────────────────────────────────────

    public function test_finance_lifecycle_audit_and_outbox(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $invoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-AUDIT-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal_cents' => 50000,
            'discount_cents' => 0,
            'tax_cents' => 9000,
            'grand_total_cents' => 59000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Draft->value,
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        $invoiceLifecycle = app(InvoiceLifecycleService::class);

        // Fire creation event
        $invoiceLifecycle->recordCreation($invoice);

        // Assert outbox recorded
        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Invoice',
            'aggregate_id' => $invoice->id,
            'event_name' => 'finance.invoice.created.v1',
        ]);

        // Assert audit event logged
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'finance.invoice.created',
        ]);

        // Fire issue event
        $invoiceLifecycle->recordIssue($invoice);

        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Invoice',
            'aggregate_id' => $invoice->id,
            'event_name' => 'finance.invoice.issued.v1',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'finance.invoice.issued',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // 3. Search Reindexing Job Dispatch
    // ─────────────────────────────────────────────────────

    public function test_finance_reindexing_job_dispatch(): void
    {
        Queue::fake();

        $this->actingAs($this->user1);
        ContextManager::boot();

        $invoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-SEARCH-001',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal_cents' => 50000,
            'discount_cents' => 0,
            'tax_cents' => 9000,
            'grand_total_cents' => 59000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Draft->value,
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        // Fire InvoiceCreated event
        event(new \App\Modules\Finance\Domain\Events\InvoiceCreated(
            entityClass: Invoice::class,
            aggregateId: $invoice->id,
            aggregateVersion: 1,
            data: $invoice->toArray(),
        ));

        // Assert search index job is dispatched
        Queue::assertPushed(UpdateIndexDocumentJob::class, function ($job) use ($invoice) {
            return $job->entityId === $invoice->id;
        });
    }

    // ─────────────────────────────────────────────────────
    // 4. Revenue Report Tenant Scoping
    // ─────────────────────────────────────────────────────

    public function test_revenue_report_tenant_scoping(): void
    {
        // Invoice under Org 1
        $this->actingAs($this->user1);
        ContextManager::boot();

        Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-RPT-ORG1',
            'customer_id' => $this->user1->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal_cents' => 100000,
            'discount_cents' => 0,
            'tax_cents' => 18000,
            'grand_total_cents' => 118000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Issued->value,
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        // Invoice under Org 2
        $this->actingAs($this->user2);
        ContextManager::boot();

        Invoice::create([
            'id' => (string) Str::uuid(),
            'invoice_number' => 'INV-RPT-ORG2',
            'customer_id' => $this->user2->id,
            'branch_id' => $this->branch->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal_cents' => 200000,
            'discount_cents' => 0,
            'tax_cents' => 36000,
            'grand_total_cents' => 236000,
            'currency' => 'INR',
            'status' => InvoiceStatus::Paid->value,
            'invoice_type' => 'tax_invoice',
            'booking_snapshot' => [],
        ]);

        // Report under Org 1 context – must NOT see Org 2's invoice
        $this->actingAs($this->user1);
        ContextManager::boot();

        $report = new RevenueReport();
        $params = ReportParameters::fromArray([]);
        $result = $report->generate($params);

        $this->assertEquals(1, $result['summary']['invoice_count']);
        $this->assertEquals(118000, $result['summary']['total_revenue_cents']);
        $this->assertCount(1, $result['records']);
        $this->assertEquals('INV-RPT-ORG1', $result['records'][0]['invoice_number']);
    }

    // ─────────────────────────────────────────────────────
    // 5. Payment Lifecycle Event
    // ─────────────────────────────────────────────────────

    public function test_payment_lifecycle_outbox(): void
    {
        $this->actingAs($this->user1);
        ContextManager::boot();

        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'paymentable_id' => (string) Str::uuid(),
            'paymentable_type' => Invoice::class,
            'payment_method' => 'upi',
            'amount_cents' => 50000,
            'reference_number' => 'PAY-REF-001',
            'status' => 'verified',
            'recorded_by' => $this->user1->id,
        ]);

        $paymentLifecycle = app(PaymentLifecycleService::class);
        $paymentLifecycle->recordReceived($payment);

        // Assert outbox recorded
        $this->assertDatabaseHas('outbox_events', [
            'aggregate_type' => 'Payment',
            'aggregate_id' => $payment->id,
            'event_name' => 'finance.payment.received.v1',
        ]);

        // Assert audit event logged
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'finance.payment.received',
        ]);
    }
}
