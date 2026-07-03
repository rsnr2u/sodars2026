<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Core\Contracts\FileStorageManagerInterface;
use Illuminate\Support\Facades\Storage;

class FileStorageManager implements FileStorageManagerInterface
{
    protected string $disk;

    public function __construct()
    {
        // Resolved dynamically based on config/settings (e.g. 'local', 's3', 'r2')
        $this->disk = config('filesystems.default', 'local');
    }

    /**
     * Upload file bytes or source paths to the target storage disk.
     */
    public function upload(string $path, mixed $fileContent): string
    {
        Storage::disk($this->disk)->put($path, $fileContent);

        return $path;
    }

    /**
     * Retrieve the public URL mapping for a given file path.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Delete file from target storage disk.
     */
    public function delete(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }
}
