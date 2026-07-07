<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Modules\Providers\Application\DTOs\UploadDocumentData;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\AssetVersion;
use App\Platform\DAM\Domain\Entities\StoredFile;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use App\Platform\DAM\Domain\Enums\AssetType;
use Illuminate\Support\Str;

class UploadDocumentAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderWriteRepositoryInterface $providerWriteRepo,
        protected ProviderLifecycleService $lifecycleService
    ) {}

    /**
     * Upload a compliance document.
     */
    public function execute(string $providerId, UploadDocumentData $data): ProviderDocument
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        // Mark any previous document of the same type as not current
        ProviderDocument::where('provider_id', $providerId)
            ->where('document_type', $data->documentType)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $latestVersion = ProviderDocument::where('provider_id', $providerId)
            ->where('document_type', $data->documentType)
            ->max('version') ?? 0;

        // 1. Create StoredFile (physical representation)
        $storedFile = StoredFile::create([
            'id' => (string) Str::uuid(),
            'storage_provider' => 'local',
            'disk' => 'public',
            'path' => $data->filePath,
            'checksum_sha256' => hash('sha256', $data->filePath),
            'checksum_md5' => md5($data->filePath),
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        // 2. Create Asset (logical representation)
        $asset = Asset::create([
            'id' => (string) Str::uuid(),
            'title' => basename($data->filePath),
            'asset_type' => AssetType::DOCUMENT,
            'status' => AssetStatus::READY,
        ]);

        // 3. Create Version
        $version = AssetVersion::create([
            'id' => (string) Str::uuid(),
            'asset_id' => $asset->id,
            'file_id' => $storedFile->id,
            'version_number' => 1,
        ]);
        $asset->update(['current_version_id' => $version->id]);

        // 4. Create ProviderDocument pointing to the asset
        /** @var ProviderDocument $doc */
        $doc = ProviderDocument::create([
            'provider_id' => $providerId,
            'asset_id' => $asset->id,
            'document_type' => $data->documentType,
            'status' => DocumentStatus::Pending->value,
            'version' => $latestVersion + 1,
            'is_current' => true,
        ]);

        if ($provider->status->value === 'draft') {
            $this->providerWriteRepo->update($providerId, ['status' => 'pending']);
        }

        $eventData = [
            'provider_id' => $providerId,
            'document_id' => $doc->id,
            'asset_id' => $asset->id,
            'document_type' => $data->documentType,
            'version' => $doc->version,
        ];

        // 5. Delegate to canonical lifecycle service
        $this->lifecycleService->recordDocumentUpload($provider, $eventData);

        return $doc;
    }
}
