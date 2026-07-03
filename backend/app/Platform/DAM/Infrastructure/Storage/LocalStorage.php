<?php

declare(strict_types=1);

namespace App\Platform\DAM\Infrastructure\Storage;

use App\Platform\DAM\Domain\Contracts\StorageProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class LocalStorage implements StorageProvider
{
    protected string $diskName = 'public';

    public function store(UploadedFile $file, string $destinationPath): string
    {
        // Store physical file using core storage facade
        $storedPath = Storage::disk($this->diskName)->putFileAs(
            $destinationPath,
            $file,
            $file->hashName()
        );

        if (!$storedPath) {
            throw new \RuntimeException("Failed to physically write file to disk [{$this->diskName}].");
        }

        return $storedPath;
    }

    public function retrieve(string $path): string
    {
        $content = Storage::disk($this->diskName)->get($path);
        if ($content === null) {
            throw new \RuntimeException("File not found on storage disk [{$path}].");
        }
        return $content;
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->diskName)->delete($path);
    }

    public function generateTemporaryUrl(string $path, int $expiresMinutes = 15): string
    {
        // Generate temporary URL stub, fallback to static URL for local storage if driver doesn't support temporary URL directly
        try {
            return Storage::disk($this->diskName)->temporaryUrl(
                $path,
                now()->addMinutes($expiresMinutes)
            );
        } catch (\Throwable $e) {
            // Local public driver does not support temporary signed URLs by default, generate a secure signed route URL
            return route('dam.assets.signed-download', [
                'path' => $path,
                'expires' => now()->addMinutes($expiresMinutes)->timestamp
            ]);
        }
    }

    public function getUrl(string $path): string
    {
        return Storage::disk($this->diskName)->url($path);
    }
}
