<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class ImageOptimizationService
{
    private const QUALITY = 80;
    private const THUMBNAIL_WIDTH = 300;
    private const MEDIUM_WIDTH = 600;

    public function optimize(UploadedFile $file, string $path): array
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $image = Image::make($file);

        // Original image with optimization
        $original = $this->saveOptimized($image, "{$path}/original/{$filename}");

        // Medium size
        $medium = $this->createResized($image, $path, $filename, self::MEDIUM_WIDTH, 'medium');

        // Thumbnail
        $thumbnail = $this->createResized($image, $path, $filename, self::THUMBNAIL_WIDTH, 'thumbnail');

        return [
            'original' => $original,
            'medium' => $medium,
            'thumbnail' => $thumbnail
        ];
    }

    private function saveOptimized($image, string $path): string
    {
        $image->encode(null, self::QUALITY);
        Storage::put("public/{$path}", $image->stream());
        return Storage::url($path);
    }

    private function createResized($image, string $path, string $filename, int $width, string $size): string
    {
        $resized = clone $image;
        $resized->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        return $this->saveOptimized($resized, "{$path}/{$size}/{$filename}");
    }

    public function deleteImages(string $path): void
    {
        Storage::delete([
            "public/{$path}/original",
            "public/{$path}/medium",
            "public/{$path}/thumbnail"
        ]);
    }
} 