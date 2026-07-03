<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Jobs;

use App\Platform\DAM\Domain\Contracts\ImageConversionStrategy;
use App\Platform\DAM\Domain\Contracts\StorageProvider;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\AssetConversion;
use App\Platform\DAM\Domain\Entities\AssetVersion;
use App\Platform\DAM\Domain\Entities\StoredFile;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class ProcessAssetConversions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $assetId;
    protected string $versionId;

    public function __construct(string $assetId, string $versionId)
    {
        $this->assetId = $assetId;
        $this->versionId = $versionId;
    }

    public function handle(
        StorageProvider $storage,
        ImageConversionStrategy $converter
    ): void {
        $asset = Asset::find($this->assetId);
        $version = AssetVersion::find($this->versionId);

        if (!$asset || !$version) {
            return;
        }

        $asset->update(['status' => AssetStatus::PROCESSING]);

        try {
            $originalFile = $version->file;
            $mime = $originalFile->mime_type;

            // Only run conversions on images
            if (str_starts_with($mime, 'image/')) {
                // Fetch physical file content and write it locally as a temporary file
                $tempSource = tempnam(sys_get_temp_dir(), 'dam_src_');
                file_put_contents($tempSource, $storage->retrieve($originalFile->path));

                // Generate thumbnail conversion profile (200x200)
                $this->generateConversion(
                    $asset,
                    $version,
                    $tempSource,
                    'thumbnail',
                    200,
                    200,
                    $storage,
                    $converter
                );

                // Generate optimized conversion profile (1200x1200)
                $this->generateConversion(
                    $asset,
                    $version,
                    $tempSource,
                    'webp_optimized',
                    1200,
                    1200,
                    $storage,
                    $converter
                );

                // Cleanup temp source
                @unlink($tempSource);
            }

            $asset->update(['status' => AssetStatus::READY]);
        } catch (\Throwable $e) {
            if (app()->environment('testing')) {
                throw $e;
            }
            logger()->error("DAM processing conversions failed for Asset [{$this->assetId}]: " . $e->getMessage());
            $asset->update(['status' => AssetStatus::FAILED]);
        }
    }

    protected function generateConversion(
        Asset $asset,
        AssetVersion $version,
        string $tempSource,
        string $conversionName,
        int $width,
        int $height,
        StorageProvider $storage,
        ImageConversionStrategy $converter
    ): void {
        $tempDest = tempnam(sys_get_temp_dir(), 'dam_dst_');
        
        $converted = $converter->convert($tempSource, $tempDest, [
            'width' => $width,
            'height' => $height,
            'format' => 'webp',
            'quality' => 75,
            'proportional' => true,
        ]);

        if ($converted && file_exists($tempDest)) {
            // Upload to storage
            $uploadedFile = new UploadedFile(
                $tempDest,
                basename($tempDest) . '.webp',
                'image/webp',
                null,
                true
            );

            $destinationDirectory = 'dam/conversions/' . date('Y/m');
            $storedPath = $storage->store($uploadedFile, $destinationDirectory);

            $sha256 = hash_file('sha256', $tempDest);
            $md5 = hash_file('md5', $tempDest);
            $size = filesize($tempDest);

            // Fetch final dimensions of conversion
            $dimensions = @getimagesize($tempDest);
            $finalWidth = $dimensions ? $dimensions[0] : $width;
            $finalHeight = $dimensions ? $dimensions[1] : $height;

            // Create StoredFile
            $storedFile = StoredFile::create([
                'id' => (string) Str::uuid(),
                'storage_provider' => 'local',
                'disk' => 'public',
                'path' => $storedPath,
                'checksum_sha256' => $sha256,
                'checksum_md5' => $md5,
                'mime_type' => 'image/webp',
                'file_size' => $size,
                'width' => $finalWidth,
                'height' => $finalHeight,
                'orientation' => $finalWidth >= $finalHeight ? 'landscape' : 'portrait',
            ]);

            // Save AssetConversion
            AssetConversion::create([
                'id' => (string) Str::uuid(),
                'asset_id' => $asset->id,
                'version_id' => $version->id,
                'file_id' => $storedFile->id,
                'conversion_name' => $conversionName,
            ]);
        }

        @unlink($tempDest);
    }
}
