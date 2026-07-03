<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageProvider
{
    /**
     * Store the uploaded file physically.
     * Returns the relative path.
     */
    public function store(UploadedFile $file, string $destinationPath): string;

    /**
     * Retrieve the full file contents.
     */
    public function retrieve(string $path): string;

    /**
     * Delete the file physically.
     */
    public function delete(string $path): bool;

    /**
     * Generate a signed temporary URL for the resource.
     */
    public function generateTemporaryUrl(string $path, int $expiresMinutes = 15): string;

    /**
     * Get the public URL.
     */
    public function getUrl(string $path): string;
}
