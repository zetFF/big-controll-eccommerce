<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends BaseController
{
    public function __construct(
        private FileService $fileService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum']);
        $this->middleware('admin')->except(['index', 'show', 'download']);
    }

    public function index(Request $request)
    {
        $files = File::with('creator')
            ->when(!auth()->user()->is_admin, fn($q) => $q->visible())
            ->when($request->type, fn($q) => $q->ofType($request->type))
            ->when($request->search, fn($q) => $q->where('name', 'LIKE', "%{$request->search}%"))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($files);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:' . config('filesystems.max_upload_size', 10240),
            'directory' => 'nullable|string',
            'visibility' => 'nullable|in:' . implode(',', File::VISIBILITIES),
            'metadata' => 'nullable|array'
        ]);

        $files = $this->fileService->storeMany(
            $request->file('files'),
            $request->directory ?? 'uploads'
        );

        return $this->successResponse($files, 201);
    }

    public function show(File $file)
    {
        if (!auth()->user()->is_admin && $file->visibility !== 'public') {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($file->load('creator'));
    }

    public function update(Request $request, File $file)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'visibility' => 'in:' . implode(',', File::VISIBILITIES),
            'metadata' => 'nullable|array'
        ]);

        $file = $this->fileService->update($file, $validated);

        return $this->successResponse($file);
    }

    public function destroy(File $file)
    {
        $this->fileService->delete($file);
        return $this->successResponse(['message' => 'File deleted successfully']);
    }

    public function download(File $file)
    {
        if (!auth()->user()->is_admin && $file->visibility !== 'public') {
            return $this->errorResponse('Unauthorized', 403);
        }

        return Storage::disk($file->disk)->download(
            $file->path,
            $file->original_name
        );
    }

    public function duplicate(Request $request, File $file)
    {
        $request->validate([
            'name' => 'nullable|string|max:255'
        ]);

        $duplicate = $this->fileService->duplicate($file, $request->name);

        return $this->successResponse($duplicate, 201);
    }

    public function move(Request $request, File $file)
    {
        $request->validate([
            'directory' => 'required|string'
        ]);

        $file = $this->fileService->move($file, $request->directory);

        return $this->successResponse($file);
    }
} 