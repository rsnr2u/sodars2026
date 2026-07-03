<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Database\Seeders;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Entities\CampaignSchedule;
use App\Modules\Campaigns\Domain\Entities\CampaignNote;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use App\Modules\Campaigns\Domain\Enums\ProofStatus;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        $customer = User::role('customer_admin')->first() ?? User::first();
        $face = InventoryFace::first();

        if (!$branch || !$customer || !$face) {
            $this->command->warn('Skipping CampaignSeeder: Dependencies (Branch, User/Customer, InventoryFace) missing.');
            return;
        }

        // 1. Create a draft campaign
        $campaign1 = Campaign::create([
            'id' => (string) Str::uuid(),
            'booking_id' => null,
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'campaign_code' => 'CMP-000001',
            'name' => 'Summer Electronics Launch',
            'description' => 'Targeting key billboards across Hitec City.',
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(21)->toDateString(),
            'status' => CampaignStatus::Draft->value,
            'objectives' => ['reach' => '100k', 'frequency' => '3x'],
            'budget_cents' => 5000000,
            'currency' => 'INR',
        ]);

        $campaign1->inventoryFaces()->attach($face->id, [
            'id' => (string) Str::uuid(),
        ]);

        // Creative
        $creative = CampaignCreative::create([
            'id' => (string) Str::uuid(),
            'campaign_id' => $campaign1->id,
            'file_name' => 'summer_launch.png',
            'file_path' => 'uploads/campaigns/creatives/summer_launch.png',
            'file_type' => 'png',
            'file_size_bytes' => 1048576,
            'version' => 1,
            'status' => CreativeStatus::Pending->value,
        ]);

        MediaLibrary::create([
            'id' => (string) Str::uuid(),
            'file_name' => 'summer_launch.png',
            'file_path' => 'uploads/campaigns/creatives/summer_launch.png',
            'mime_type' => 'image/png',
            'file_size_bytes' => 1048576,
            'mediable_type' => CampaignCreative::class,
            'mediable_id' => $creative->id,
        ]);

        CampaignNote::create([
            'id' => (string) Str::uuid(),
            'campaign_id' => $campaign1->id,
            'user_id' => $customer->id,
            'body' => 'Original creative banner uploaded for verification.',
            'is_internal' => false,
        ]);

        // Activity log
        CampaignActivity::create([
            'id' => (string) Str::uuid(),
            'campaign_id' => $campaign1->id,
            'performed_by' => $customer->id,
            'event_name' => 'campaign.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $campaign1->toArray(),
            'ip' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'trace_id' => (string) Str::uuid(),
        ]);
    }
}
