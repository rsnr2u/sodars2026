<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Services;

use App\Platform\DAM\Application\Actions\UploadAssetAction;
use App\Platform\DAM\Application\Actions\AttachAssetAction;
use App\Platform\DAM\Application\Actions\CreateFolderAction;
use App\Platform\DAM\Domain\Contracts\StorageProvider;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\Attachment;
use App\Platform\DAM\Domain\Entities\Folder;
use App\Platform\DAM\Domain\Enums\AttachmentRole;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DAMService
{
    protected UploadAssetAction $uploadAction;
    protected AttachAssetAction $attachAction;
    protected CreateFolderAction $createFolderAction;
    protected StorageProvider $storage;

    public function __construct(
        UploadAssetAction $uploadAction,
        AttachAssetAction $attachAction,
        CreateFolderAction $createFolderAction,
        StorageProvider $storage
    ) {
        $this->uploadAction = $uploadAction;
        $this->attachAction = $attachAction;
        $this->createFolderAction = $createFolderAction;
        $this->storage = $storage;
    }

    /**
     * Upload an asset.
     */
    public function upload(
        UploadedFile $file,
        string $title,
        ?string $description = null,
        ?string $folderId = null
    ): Asset {
        return $this->uploadAction->execute($file, $title, $description, $folderId);
    }

    /**
     * Attach an asset polymorphically.
     */
    public function attach(string $assetId, Model $entity, AttachmentRole $role): Attachment
    {
        return $this->attachAction->execute($assetId, $entity, $role);
    }

    /**
     * Create a directory folder.
     */
    public function createFolder(string $name, ?string $parentId = null): Folder
    {
        return $this->createFolderAction->execute($name, $parentId);
    }

    /**
     * Generate temporary signed URLs.
     */
    public function generateTemporaryUrl(string $path, int $expiresMinutes = 15): string
    {
        return $this->storage->generateTemporaryUrl($path, $expiresMinutes);
    }

    /**
     * Retrieve static public URL of a physical path.
     */
    public function getUrl(string $path): string
    {
        return $this->storage->getUrl($path);
    }

    /**
     * Retrieve an asset model.
     */
    public function find(string $assetId): Asset
    {
        return Asset::with(['currentVersion.file', 'versions.file'])->findOrFail($assetId);
    }
}
