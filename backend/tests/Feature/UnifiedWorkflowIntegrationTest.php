<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Transport\Domain\Entities\Route;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\Entities\WorkflowTask;
use App\Platform\Workflows\Domain\Enums\WorkflowStatus;
use App\Platform\Workflows\Domain\Enums\TaskStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use App\Platform\Accounting\Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class UnifiedWorkflowIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected WorkflowEngineService $engine;
    protected Branch $branch;
    protected User $user;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->engine = app(WorkflowEngineService::class);
        $this->user = User::factory()->create();

        // 1. Create Organization & Seed Chart of Accounts (COA) for Wallet/Accounting
        $this->org = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Enterprise Org',
            'slug' => 'hq-enterprise',
            'domain' => 'hq.sodars2026.com',
            'is_active' => true,
        ]);

        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->org->id, null);
        $this->seed(ChartOfAccountsSeeder::class);

        // 2. Create Branch
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Branch',
            'code' => 'HQ-B',
            'support_email' => 'hq@sodars.com',
            'support_phone' => '+91800100',
        ]);
    }

    public function test_campaign_approval_workflow(): void
    {
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);

        $dsl = [
            'key' => 'campaign.approval',
            'states' => ['Ready', 'Approved', 'Cancelled'],
            'initial_state' => 'Ready',
            'steps' => [
                [
                    'name' => 'Manager Approval',
                    'role' => 'branch_manager',
                    'order' => 1,
                    'sla_hours' => 24,
                    'approval_mode' => 'any',
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'Ready',
                    'to' => 'Approved',
                ],
                [
                    'name' => 'reject',
                    'from' => 'Ready',
                    'to' => 'Cancelled',
                ]
            ]
        ];

        $publisher->publish('Campaign Approval', 'campaign.approval', Campaign::class, $dsl);

        $campaign = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Winter Promo',
            'campaign_code' => 'CMP-WINTER-001',
            'customer_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => CampaignStatus::Ready,
        ]);

        $instance = $this->engine->start('campaign.approval', Campaign::class, $campaign->id, [
            'campaign_code' => $campaign->campaign_code,
            'organization_id' => $this->org->id,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        $manager = User::factory()->create();
        $manager->assignRole('branch_manager');
        $this->actingAs($manager);

        // Approve Campaign
        $this->engine->actionTask($task->id, 'approve', $manager->id, 'Approved Campaign.');

        $campaign->refresh();
        $this->assertEquals(CampaignStatus::Approved, $campaign->status);

        $instance->refresh();
        $this->assertEquals(WorkflowStatus::Completed, $instance->status);
    }

    public function test_provider_verification_workflow(): void
    {
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);

        $dsl = [
            'key' => 'provider.verification',
            'states' => ['Pending', 'Verified', 'Draft'],
            'initial_state' => 'Pending',
            'steps' => [
                [
                    'name' => 'Compliance Check',
                    'role' => 'branch_manager',
                    'order' => 1,
                    'sla_hours' => 24,
                    'approval_mode' => 'any',
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'Pending',
                    'to' => 'Verified',
                ],
                [
                    'name' => 'reject',
                    'from' => 'Pending',
                    'to' => 'Draft',
                ]
            ]
        ];

        $publisher->publish('Provider Verification', 'provider.verification', Provider::class, $dsl);

        $provider = Provider::create([
            'company_name' => 'Goliath Billboards',
            'registration_number' => 'REG-GOLIATH-101',
            'provider_code' => 'GOL-PRV-01',
            'status' => ProviderStatus::Pending->value,
            'default_branch_id' => $this->branch->id,
        ]);

        $instance = $this->engine->start('provider.verification', Provider::class, $provider->id, [
            'provider_code' => $provider->provider_code,
            'organization_id' => $this->org->id,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        $officer = User::factory()->create();
        $officer->assignRole('branch_manager');

        $this->actingAs($officer);
        $this->engine->actionTask($task->id, 'approve', $officer->id, 'Provider complies.');

        $provider->refresh();
        $this->assertEquals(ProviderStatus::Verified->value, $provider->status->value ?? $provider->status);
    }

    public function test_wallet_withdrawal_workflow_rejection_refunds_balance(): void
    {
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);

        $dsl = [
            'key' => 'wallet.withdrawal_approval',
            'states' => ['Requested', 'Completed', 'Rejected'],
            'initial_state' => 'Requested',
            'steps' => [
                [
                    'name' => 'Finance Director Review',
                    'role' => 'super_admin',
                    'order' => 1,
                    'sla_hours' => 12,
                    'approval_mode' => 'any',
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'Requested',
                    'to' => 'Completed',
                ],
                [
                    'name' => 'reject',
                    'from' => 'Requested',
                    'to' => 'Rejected',
                ]
            ]
        ];

        $publisher->publish('Withdrawal Approval', 'wallet.withdrawal_approval', Withdrawal::class, $dsl);

        $provider = Provider::create([
            'company_name' => 'Goliath Billboards 2',
            'registration_number' => 'REG-GOLIATH-102',
            'provider_code' => 'GOL-PRV-02',
            'status' => ProviderStatus::Verified->value,
            'default_branch_id' => $this->branch->id,
        ]);

        $walletService = app(WalletService::class);
        $wallet = $walletService->createWallet($provider, 'provider', 'INR');
        $walletService->deposit($wallet, 500000, 'DEP-INIT'); // 5000 INR

        // Request Withdrawal
        $withdrawal = $walletService->requestWithdrawal($wallet, 300000, [
            'account_number' => '987654321',
            'bank_name' => 'National Bank',
        ]);

        // Dynamic Balance after requested withdrawal should be 5000 INR since not completed yet
        $this->assertEquals(500000, $walletService->calculateDynamicBalance($wallet->id));

        $instance = $this->engine->start('wallet.withdrawal_approval', Withdrawal::class, $withdrawal->id, [
            'withdrawal_id' => $withdrawal->id,
            'organization_id' => $this->org->id,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Reject task (restores balance capacity for other requests, dynamic remains at 5000 INR)
        $this->engine->actionTask($task->id, 'reject', $admin->id, 'Rejected due to validation limits.');

        $withdrawal->refresh();
        $this->assertEquals(WithdrawalStatus::Rejected->value, $withdrawal->status->value ?? $withdrawal->status);

        $this->assertEquals(500000, $walletService->calculateDynamicBalance($wallet->id));
    }

    public function test_wallet_withdrawal_workflow_approval_posts_ledger_deduction(): void
    {
        $publisher = app(\App\Platform\Workflows\Domain\Services\WorkflowDefinitionPublisher::class);

        $dsl = [
            'key' => 'wallet.withdrawal_approval',
            'states' => ['Requested', 'Completed', 'Rejected'],
            'initial_state' => 'Requested',
            'steps' => [
                [
                    'name' => 'Finance Director Review',
                    'role' => 'super_admin',
                    'order' => 1,
                    'sla_hours' => 12,
                    'approval_mode' => 'any',
                ]
            ],
            'transitions' => [
                [
                    'name' => 'approve',
                    'from' => 'Requested',
                    'to' => 'Completed',
                ],
                [
                    'name' => 'reject',
                    'from' => 'Requested',
                    'to' => 'Rejected',
                ]
            ]
        ];

        $publisher->publish('Withdrawal Approval', 'wallet.withdrawal_approval', Withdrawal::class, $dsl);

        $provider = Provider::create([
            'company_name' => 'Goliath Billboards 3',
            'registration_number' => 'REG-GOLIATH-103',
            'provider_code' => 'GOL-PRV-03',
            'status' => ProviderStatus::Verified->value,
            'default_branch_id' => $this->branch->id,
        ]);

        $walletService = app(WalletService::class);
        $wallet = $walletService->createWallet($provider, 'provider', 'INR');
        $walletService->deposit($wallet, 500000, 'DEP-INIT'); // 5000 INR

        // Request Withdrawal
        $withdrawal = $walletService->requestWithdrawal($wallet, 300000, [
            'account_number' => '987654321',
            'bank_name' => 'National Bank',
        ]);

        $instance = $this->engine->start('wallet.withdrawal_approval', Withdrawal::class, $withdrawal->id, [
            'withdrawal_id' => $withdrawal->id,
            'organization_id' => $this->org->id,
        ]);

        $task = WorkflowTask::where('instance_id', $instance->id)->firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Approve task (completes withdrawal, posts to ledger)
        $this->engine->actionTask($task->id, 'approve', $admin->id, 'Payout completed.');

        $withdrawal->refresh();
        $this->assertEquals(WithdrawalStatus::Completed->value, $withdrawal->status->value ?? $withdrawal->status);

        // Dynamic Balance after completion is now dynamically computed as 2000 INR (5000 - 3000)
        $this->assertEquals(200000, $walletService->calculateDynamicBalance($wallet->id));
    }
}
