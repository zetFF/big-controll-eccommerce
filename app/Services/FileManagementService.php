<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagementService
{
    public function uploadFile(UploadedFile $file, ?Folder $folder = null, array $metadata = []): File
    {
        $fileName = $this->generateFileName($file);
        $path = $folder ? $folder->full_path . '/' . $fileName : $fileName;

        Storage::disk('public')->putFileAs(
            $folder?->full_path ?? '',
            $file,
            $fileName
        );

        return File::create([
            'name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'uploaded_by' => auth()->id(),
            'metadata' => $metadata,
            'folder_id' => $folder?->id,
        ]);
    }

    public function createFolder(string $name, ?Folder $parent = null, array $metadata = []): Folder
    {
        return Folder::create([
            'name' => $name,
            'parent_id' => $parent?->id,
            'created_by' => auth()->id(),
            'metadata' => $metadata,
        ]);
    }

    public function moveFile(File $file, ?Folder $newFolder = null): File
    {
        $oldPath = $file->full_path;
        $newPath = $newFolder 
            ? $newFolder->full_path . '/' . $file->name
            : $file->name;

        if ($oldPath !== $newPath) {
            Storage::disk($file->disk)->move($oldPath, $newPath);
            $file->update([
                'path' => $newPath,
                'folder_id' => $newFolder?->id,
            ]);
        }

        return $file;
    }

    public function moveFolder(Folder $folder, ?Folder $newParent = null): Folder
    {
        if ($newParent && $this->wouldCreateCycle($folder, $newParent)) {
            throw new \Exception('Moving folder would create a cycle');
        }

        $oldPath = $folder->full_path;
        $folder->update(['parent_id' => $newParent?->id]);
        $newPath = $folder->full_path;

        if ($oldPath !== $newPath) {
            $this->moveFilesRecursively($folder, $oldPath, $newPath);
        }

        return $folder;
    }

    public function deleteFile(File $file): void
    {
        Storage::disk($file->disk)->delete($file->full_path);
        $file->delete();
    }

    public function deleteFolder(Folder $folder): void
    {
        $folder->files()->chunk(100, function ($files) {
            foreach ($files as $file) {
                $this->deleteFile($file);
            }
        });

        $folder->children()->chunk(100, function ($children) {
            foreach ($children as $child) {
                $this->deleteFolder($child);
            }
        });

        $folder->delete();
    }

    private function generateFileName(UploadedFile $file): string
    {
        return sprintf(
            '%s_%s.%s',
            Str::random(20),
            time(),
            $file->getClientOriginalExtension()
        );
    }

    private function wouldCreateCycle(Folder $folder, Folder $newParent): bool
    {
        if ($folder->id === $newParent->id) {
            return true;
        }

        $current = $newParent;
        while ($current->parent_id) {
            if ($current->parent_id === $folder->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    private function moveFilesRecursively(Folder $folder, string $oldPath, string $newPath): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($oldPath)) {
            $files = $disk->files($oldPath);
            $directories = $disk->directories($oldPath);

            foreach ($files as $file) {
                $relativePath = Str::after($file, $oldPath . '/');
                $disk->move($file, $newPath . '/' . $relativePath);
            }

            foreach ($directories as $directory) {
                $relativePath = Str::after($directory, $oldPath . '/');
                $this->moveFilesRecursively(
                    $folder,
                    $oldPath . '/' . $relativePath,
                    $newPath . '/' . $relativePath
                );
            }
        }
    }
} 