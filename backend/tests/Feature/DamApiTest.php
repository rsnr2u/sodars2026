<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Platform\DAM\Application\Jobs\ProcessAssetConversions;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\Folder;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use App\Platform\DAM\Domain\Enums\AssetType;
use App\Platform\DAM\Domain\Enums\AttachmentRole;
use Database\Seeders\GeographySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\DamSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class DamApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GeographySeeder::class);
        $this->seed(DamSeeder::class);
        
        Storage::fake('public');
    }

    public function test_asset_upload_and_metadata_extraction(): void
    {
        $this->actingAsAdmin();
        Queue::fake();

        // 1. Prepare fake image file
        $file = UploadedFile::fake()->image('billboard_summer.jpg', 1200, 600);
        $folder = Folder::where('name', 'Campaign Creatives')->first();

        // 2. Post upload request
        $response = $this->postJson('/api/v1/dam/assets', [
            'file' => $file,
            'title' => 'Summer Campaign Creative',
            'description' => 'Creative layout for Highway Billboard',
            'folder_id' => $folder->id,
        ]);

        $this->assertApiResponse($response, 201);
        $response->assertJsonPath('data.title', 'Summer Campaign Creative');
        $response->assertJsonPath('data.asset_type', AssetType::IMAGE->value);
        $response->assertJsonPath('data.status', AssetStatus::PROCESSING->value);
        $response->assertJsonPath('data.file.width', 1200);
        $response->assertJsonPath('data.file.height', 600);
        $response->assertJsonPath('data.file.orientation', 'landscape');
        
        $assetId = $response->json('data.id');
        $asset = Asset::findOrFail($assetId);
        $this->assertNotNull($asset->currentVersion->file->checksum_sha256);
        $this->assertNotNull($asset->currentVersion->file->checksum_md5);

        // 3. Verify conversion background job was dispatched
        Queue::assertPushed(ProcessAssetConversions::class);
    }

    public function test_conversions_job_generates_webp_and_thumbnails(): void
    {
        $this->actingAsAdmin();
        Queue::fake();

        // Upload an image physically first
        $file = UploadedFile::fake()->image('metro_creative.jpg', 800, 400);
        
        $action = app(\App\Platform\DAM\Application\Actions\UploadAssetAction::class);
        $asset = $action->execute($file, 'Metro Creative Billboard');

        $this->assertEquals(AssetStatus::PROCESSING, $asset->status);

        // Directly run the conversions job
        $job = new ProcessAssetConversions($asset->id, $asset->current_version_id);
        $job->handle(
            app(\App\Platform\DAM\Domain\Contracts\StorageProvider::class),
            app(\App\Platform\DAM\Domain\Contracts\ImageConversionStrategy::class)
        );

        // Fetch asset state after job execution
        $updatedAsset = Asset::with([
            'currentVersion.file',
            'versions.conversions.file'
        ])->findOrFail($asset->id);

        $this->assertEquals(AssetStatus::READY, $updatedAsset->status);

        // Check if thumbnail and webp conversions were created
        $conversions = $updatedAsset->versions->first()->conversions;
        $this->assertCount(2, $conversions);

        $thumbnail = $conversions->where('conversion_name', 'thumbnail')->first();
        $this->assertNotNull($thumbnail);
        $this->assertEquals('image/webp', $thumbnail->file->mime_type);
        $this->assertTrue($thumbnail->file->width <= 200);

        $webpOpt = $conversions->where('conversion_name', 'webp_optimized')->first();
        $this->assertNotNull($webpOpt);
        $this->assertEquals('image/webp', $webpOpt->file->mime_type);
        $this->assertTrue($webpOpt->file->width <= 1200);
    }

    public function test_polymorphic_attachment_and_signed_url_generation(): void
    {
        $admin = $this->actingAsAdmin();

        $branch = \App\Modules\Branches\Domain\Entities\Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'HQ Sales Branch',
            'code' => 'HQ-SB',
            'support_email' => 'sales@sodars.com',
            'support_phone' => '+91800100300',
        ]);

        // Create a test campaign to link to
        $campaign = Campaign::create([
            'id' => (string) Str::uuid(),
            'name' => 'Corporate Summer Push',
            'customer_id' => $admin->id,
            'branch_id' => $branch->id,
            'campaign_code' => 'CMP-' . Str::upper(Str::random(6)),
            'budget_cents' => 1000000,
            'currency' => 'INR',
            'status' => 'draft',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $file = UploadedFile::fake()->image('kyc_file.png', 400, 400);
        $action = app(\App\Platform\DAM\Application\Actions\UploadAssetAction::class);
        $asset = $action->execute($file, 'KYC Document Profile');

        // Link asset to campaign
        $response = $this->postJson("/api/v1/dam/assets/{$asset->id}/attach", [
            'attachable_type' => Campaign::class,
            'attachable_id' => $campaign->id,
            'attachment_role' => AttachmentRole::CREATIVE->value,
        ]);

        $this->assertApiResponse($response, 200);
        $response->assertJsonPath('data.attachment_role', AttachmentRole::CREATIVE->value);

        $updatedAsset = Asset::findOrFail($asset->id);
        $this->assertEquals(1, $updatedAsset->attachment_count);

        // Generate signed URL
        $signedResponse = $this->postJson("/api/v1/dam/assets/{$asset->id}/signed-url", [
            'expires_minutes' => 30,
        ]);

        $this->assertApiResponse($signedResponse, 200);
        $signedResponse->assertJsonStructure(['data' => ['url', 'expires_at']]);
    }
}
