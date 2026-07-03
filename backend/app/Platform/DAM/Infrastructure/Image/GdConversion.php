<?php

declare(strict_types=1);

namespace App\Platform\DAM\Infrastructure\Image;

use App\Platform\DAM\Domain\Contracts\ImageConversionStrategy;

class GdConversion implements ImageConversionStrategy
{
    public function convert(string $sourcePath, string $targetPath, array $options): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        // Get image details
        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return false;
        }

        $mime = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        // Load image source based on mime type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$srcImage) {
            return false;
        }

        // Determine target dimensions
        $targetWidth = $options['width'] ?? $width;
        $targetHeight = $options['height'] ?? $height;

        // Maintain aspect ratio if proportional option is active
        if ($options['proportional'] ?? true) {
            $ratio = min($targetWidth / $width, $targetHeight / $height);
            if ($ratio < 1.0) {
                $targetWidth = (int) round($width * $ratio);
                $targetHeight = (int) round($height * $ratio);
            } else {
                $targetWidth = $width;
                $targetHeight = $height;
            }
        }

        // Create canvas
        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Keep alpha transparency channels for PNG/WebP
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);

        // Copy and resample image source
        imagecopyresampled(
            $dstImage,
            $srcImage,
            0, 0, 0, 0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        // Ensure directories exist
        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $quality = $options['quality'] ?? 80;
        $format = $options['format'] ?? 'webp';

        // Write converted image file
        $success = false;
        if ($format === 'webp') {
            $success = @imagewebp($dstImage, $targetPath, $quality);
        } else {
            $success = @imagejpeg($dstImage, $targetPath, $quality);
        }

        // Clear memory resources
        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return $success;
    }
}
