<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Contracts;

interface ImageConversionStrategy
{
    /**
     * Resize or optimize an image file.
     *
     * @param string $sourcePath The absolute path to the source image.
     * @param string $targetPath The absolute path where the conversion should be written.
     * @param array $options Configuration such as width, height, format (webp/jpeg), quality.
     * @return bool
     */
    public function convert(string $sourcePath, string $targetPath, array $options): bool;
}
