<?php

declare(strict_types=1);

namespace App\Core\Contracts;

interface FileStorageManagerInterface
{
    /**
     * Upload file bytes or source paths to the target storage disk. Returns file path.
     */
    public function upload(string $path, mixed $fileContent): string;

    /**
     * Retrieve the public URL mapping for a given file path.
     */
    public function getUrl(string $path): string;

    /**
     * Delete file from target storage disk.
     */
    public function delete(string $path): bool;
}
