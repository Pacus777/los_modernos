<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImageStorageService
{
    public function storePublicImageAsWebp(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');

        if ($this->supportsWebpConversion()) {
            try {
                return $this->writeWebpToDisk($file, $directory, 'public');
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $file->store($directory, 'public');
    }

    public function supportsWebpConversion(): bool
    {
        return extension_loaded('gd')
            && function_exists('imagewebp')
            && (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng'));
    }

    private function writeWebpToDisk(UploadedFile $file, string $directory, string $disk): string
    {
        $image = $this->loadImage($file);

        if (! $image instanceof GdImage) {
            throw new RuntimeException('No se pudo decodificar la imagen subida.');
        }

        try {
            $image = $this->resizeIfNeeded($image);
            $this->prepareAlpha($image);

            $filename = Str::uuid()->toString().'.webp';
            $relativePath = $directory.'/'.$filename;

            Storage::disk($disk)->makeDirectory($directory);

            $absolutePath = Storage::disk($disk)->path($relativePath);
            $quality = (int) config('images.webp_quality', 85);
            $quality = max(0, min(100, $quality));

            if (! imagewebp($image, $absolutePath, $quality)) {
                throw new RuntimeException('No se pudo escribir el archivo WebP.');
            }

            return $relativePath;
        } finally {
            imagedestroy($image);
        }
    }

    private function loadImage(UploadedFile $file): GdImage|false
    {
        $path = $file->getRealPath();

        if ($path === false) {
            return false;
        }

        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($path)
                : false,
            default => @imagecreatefromstring((string) file_get_contents($path)),
        };
    }

    private function resizeIfNeeded(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $maxWidth = (int) config('images.max_width', 1600);
        $maxHeight = (int) config('images.max_height', 1600);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height,
        );
        imagedestroy($image);

        return $resized;
    }

    private function prepareAlpha(GdImage $image): void
    {
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);
    }
}
