<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Platform\Reporting\Domain\Entities\Dashboard;
use App\Platform\Reporting\Domain\Entities\DashboardWidget;
use App\Platform\Reporting\Domain\Entities\ScheduledReport;
use App\Platform\Reporting\Domain\Entities\ReportExecution;
use App\Platform\Reporting\Application\Jobs\RunScheduledReportJob;
use App\Platform\Accounting\Journal\LedgerEntry;
use App\Platform\Accounting\Journal\EntryType;
use App\Platform\Notifications\Domain\Entities\NotificationDispatch;
use App\Platform\Notifications\Database\Seeders\NotificationSeeder;
use App\Platform\DAM\Domain\Entities\Asset;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class ReportingApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(NotificationSeeder::class);

        // Seed notifications template for reporting
        $this->seedNotificationTemplate();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
    }

    protected function seedNotificationTemplate(): void
    {
        $template = \App\Platform\Notifications\Domain\Entities\NotificationTemplate::firstOrCreate(
            ['key' => 'scheduled_report_ready'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Scheduled Report Ready Notification',
                'category' => 'transactional',
                'active_version_number' => 1,
            ]
        );

        \App\Platform\Notifications\Domain\Entities\NotificationTemplateVersion::firstOrCreate(
            ['template_id' => $template->id, 'version_number' => 1],
            [
                'id' => (string) Str::uuid(),
                'subject' => 'Your scheduled report {{report_name}} is ready',
                'content' => [
                    'email' => [
                        'body' => 'Hello, your scheduled report {{report_name}} has been generated successfully. Download here: {{download_url}}'
                    ],
                    'in_app' => [
                        'title' => 'success',
                        'body' => 'Report {{report_name}} is ready.'
                    ]
                ],
                'is_active' => true,
            ]
        );
    }

    protected function seedAccounting(int $debitCents, int $creditCents): void
    {
        $this->seed(\App\Platform\Accounting\Database\Seeders\ChartOfAccountsSeeder::class);

        $period = \App\Platform\Accounting\ChartOfAccounts\AccountingPeriod::create([
            'id' => (string) Str::uuid(),
            'fiscal_year' => date('Y'),
            'month' => (int) date('m'),
            'status' => 'open',
        ]);

        $journal = \App\Platform\Accounting\Journal\LedgerJournal::create([
            'id' => (string) Str::uuid(),
            'reference_number' => 'REF-' . Str::random(5),
            'narration' => 'Test narration',
            'journal_type' => 'general',
            'status' => 'posted',
            'accounting_period_id' => $period->id,
        ]);

        $cashAccount = \App\Platform\Accounting\ChartOfAccounts\LedgerAccount::where('code', '1100-CASH')->firstOrFail();
        $gstAccount = \App\Platform\Accounting\ChartOfAccounts\LedgerAccount::where('code', '2200-GST-PAYABLE')->firstOrFail();

        if ($debitCents > 0) {
            LedgerEntry::create([
                'id' => (string) Str::uuid(),
                'journal_id' => $journal->id,
                'ledger_account_id' => $cashAccount->id,
                'line_number' => 1,
                'entry_type' => EntryType::Debit->value,
                'amount_cents' => $debitCents,
                'base_amount_cents' => $debitCents,
            ]);
        }

        if ($creditCents > 0) {
            LedgerEntry::create([
                'id' => (string) Str::uuid(),
                'journal_id' => $journal->id,
                'ledger_account_id' => $gstAccount->id,
                'line_number' => 2,
                'entry_type' => EntryType::Credit->value,
                'amount_cents' => $creditCents,
                'base_amount_cents' => $creditCents,
            ]);
        }
    }

    public function test_report_registry_and_run(): void
    {
        $this->actingAs($this->admin);

        $this->seedAccounting(150000, 150000);

        $response = $this->getJson('/api/v1/reports');
        $response->assertStatus(200)
            ->assertJsonFragment(['trial_balance' => 'Trial Balance Report']);

        $response = $this->getJson('/api/v1/reports/trial_balance');
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['key', 'parameters_schema']]);

        $response = $this->postJson('/api/v1/reports/trial_balance/run');
        $response->assertStatus(200)
            ->assertJsonFragment(['total_debit_cents' => 150000])
            ->assertJsonFragment(['total_credit_cents' => 150000])
            ->assertJsonFragment(['is_balanced' => true]);
    }

    public function test_report_export_to_dam(): void
    {
        $this->actingAs($this->admin);

        $this->seedAccounting(10000, 10000);

        $response = $this->postJson('/api/v1/reports/trial_balance/export');
        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['asset_id', 'title', 'download_url']]);

        $assetId = $response->json('data.asset_id');
        $this->assertDatabaseHas('dam_assets', ['id' => $assetId]);
    }

    public function test_dashboard_and_widgets_rendering(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/v1/dashboards', [
            'name' => 'Finance Dashboard',
            'is_default' => true,
        ]);
        $response->assertStatus(201);
        $dashboardId = $response->json('data.id');

        $response = $this->postJson("/api/v1/dashboards/{$dashboardId}/widgets", [
            'report_key' => 'trial_balance',
            'widget_type' => 'value_card',
            'title' => 'Ledger Balance',
            'dimensions' => ['x' => 0, 'y' => 0, 'width' => 4, 'height' => 2],
        ]);
        $response->assertStatus(201);

        $response = $this->postJson("/api/v1/dashboards/{$dashboardId}/widgets", [
            'report_key' => 'inventory_occupancy',
            'widget_type' => 'bar_chart',
            'title' => 'Inventory Status Chart',
            'dimensions' => ['x' => 0, 'y' => 2, 'width' => 8, 'height' => 4],
        ]);
        $response->assertStatus(201);

        $response = $this->getJson("/api/v1/dashboards/{$dashboardId}/widgets");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'widgets' => [
                        '*' => ['id', 'title', 'type', 'dimensions']
                    ]
                ]
            ]);
    }

    public function test_scheduled_report_delivery_and_execution_logs(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/v1/reporting/scheduled', [
            'report_key' => 'trial_balance',
            'name' => 'Weekly Trial Balance',
            'cron_expression' => '0 0 * * 1',
            'recipient_emails' => [$this->admin->email],
        ]);
        $response->assertStatus(201);
        $scheduleId = $response->json('data.id');

        $this->seedAccounting(50000, 50000);

        RunScheduledReportJob::dispatchSync($scheduleId);

        $this->assertDatabaseHas('report_executions', [
            'scheduled_report_id' => $scheduleId,
            'status' => 'completed',
        ]);

        $execution = ReportExecution::where('scheduled_report_id', $scheduleId)->first();
        $this->assertNotNull($execution->dam_asset_id);

        $this->assertDatabaseHas('notification_dispatches', [
            'recipient_id' => $this->admin->id,
            'channel' => 'email',
        ]);

        $dispatch = NotificationDispatch::where('recipient_id', $this->admin->id)->first();
        $this->assertStringContainsString('Weekly Trial Balance', $dispatch->context_snapshot['report_name']);
        $this->assertNotNull($dispatch->context_snapshot['download_url']);
    }
}
