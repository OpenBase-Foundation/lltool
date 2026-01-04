<?php
declare(strict_types=1);

namespace LLTool\Services;

use LLTool\Support\Config;

final class PhotoUploadService
{
    private string $storagePath;
    private int $maxSize;
    private array $allowedTypes;

    public function __construct()
    {
        // Try PHP directory first
        $basePath = dirname(__DIR__, 2);
        $this->storagePath = $basePath . '/storage/photos';
        
        // If not exists, try current directory
        if (!is_dir($this->storagePath)) {
            $this->storagePath = __DIR__ . '/../../storage/photos';
        }
        
        $this->maxSize = (int)(Config::get('UPLOAD_MAX_SIZE', 5242880)); // 5MB default
        $this->allowedTypes = explode(',', Config::get('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif'));
        
        // Ensure storage directory exists
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Upload and process photo.
     */
    public function upload(array $file, ?int $width = null, ?int $height = null): ?string
    {
        // Validate file
        if (!$this->validateFile($file)) {
            return null;
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->generateFilename($extension);
        $filepath = $this->storagePath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return null;
        }

        // Resize if needed
        if ($width !== null || $height !== null) {
            $this->resizeImage($filepath, $width, $height);
        }

        // Return relative URL
        return '/storage/photos/' . $filename;
    }

    /**
     * Delete photo file.
     */
    public function delete(string $photoUrl): bool
    {
        if (empty($photoUrl)) {
            return true;
        }

        // Extract filename from URL
        $filename = basename($photoUrl);
        $filepath = $this->storagePath . '/' . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return true;
    }

    /**
     * Validate uploaded file.
     */
    private function validateFile(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            return false;
        }

        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return false;
        }

        // Verify it's actually an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename.
     */
    private function generateFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * Resize image.
     */
    private function resizeImage(string $filepath, ?int $width, ?int $height): void
    {
        $imageInfo = getimagesize($filepath);
        if ($imageInfo === false) {
            return;
        }

        [$originalWidth, $originalHeight, $type] = $imageInfo;

        // Calculate new dimensions maintaining aspect ratio
        if ($width === null && $height !== null) {
            $width = (int)($originalWidth * $height / $originalHeight);
        } elseif ($height === null && $width !== null) {
            $height = (int)($originalHeight * $width / $originalWidth);
        } elseif ($width === null && $height === null) {
            return; // No resize needed
        }

        // Create image resource
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($filepath),
            IMAGETYPE_PNG => imagecreatefrompng($filepath),
            IMAGETYPE_GIF => imagecreatefromgif($filepath),
            default => null,
        };

        if ($source === null) {
            return;
        }

        // Create resized image
        $destination = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefill($destination, 0, 0, $transparent);
        }

        imagecopyresampled(
            $destination,
            $source,
            0, 0, 0, 0,
            $width, $height,
            $originalWidth, $originalHeight
        );

        // Save resized image
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($destination, $filepath, 90),
            IMAGETYPE_PNG => imagepng($destination, $filepath, 9),
            IMAGETYPE_GIF => imagegif($destination, $filepath),
            default => null,
        };

        imagedestroy($source);
        imagedestroy($destination);
    }
}

