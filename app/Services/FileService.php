<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function store(
        UploadedFile $file,
        string $directory = 'uploads',
        string $visibility = 'private',
        array $metadata = []
    ): File {
        $path = $file->store($directory, [
            'disk' => config('filesystems.default'),
            'visibility' => $visibility
        ]);

        return File::create([
            'name' => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => config('filesystems.default'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'checksum' => hash_file('md5', $file->getRealPath()),
            'visibility' => $visibility,
            'created_by' => auth()->id(),
            'metadata' => $metadata
        ]);
    }

    public function storeMany(array $files, string $directory = 'uploads'): array
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $storedFiles[] = $this->store($file, $directory);
        }

        return $storedFiles;
    }

    public function update(File $file, array $data): File
    {
        if (isset($data['visibility']) && $data['visibility'] !== $file->visibility) {
            Storage::disk($file->disk)->setVisibility(
                $file->path,
                $data['visibility']
            );
        }

        $file->update($data);
        return $file;
    }

    public function delete(File $file): bool
    {
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        return $file->delete();
    }

    public function duplicate(File $file, ?string $newName = null): File
    {
        $newPath = $this->generateUniquePath(
            dirname($file->path),
            $newName ?? $file->name,
            $file->extension
        );

        Storage::disk($file->disk)->copy($file->path, $newPath);

        return File::create([
            'name' => $newName ?? $file->name,
            'original_name' => $file->original_name,
            'path' => $newPath,
            'disk' => $file->disk,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'extension' => $file->extension,
            'checksum' => $file->checksum,
            'visibility' => $file->visibility,
            'created_by' => auth()->id(),
            'metadata' => $file->metadata
        ]);
    }

    public function move(File $file, string $newDirectory): File
    {
        $newPath = $this->generateUniquePath(
            $newDirectory,
            $file->name,
            $file->extension
        );

        Storage::disk($file->disk)->move($file->path, $newPath);
        $file->update(['path' => $newPath]);

        return $file;
    }

    private function generateUniquePath(string $directory, string $name, string $extension): string
    {
        $path = trim($directory, '/') . '/' . Str::slug($name) . '.' . $extension;
        $counter = 1;

        while (Storage::disk(config('filesystems.default'))->exists($path)) {
            $path = trim($directory, '/') . '/' . Str::slug($name) . '-' . $counter . '.' . $extension;
            $counter++;
        }

        return $path;
    }
} 