<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\FileCollection;
use App\Services\FileService;
use Illuminate\Http\Request;

class FileCollectionController extends BaseController
{
    public function __construct(
        private FileService $fileService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum']);
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $collections = FileCollection::with(['creator', 'files'])
            ->when(!auth()->user()->is_admin, fn($q) => $q->visible())
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($collections);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'visibility' => 'required|in:' . implode(',', File::VISIBILITIES),
            'files.*' => 'required|file|max:' . config('filesystems.max_upload_size', 10240),
            'metadata' => 'nullable|array'
        ]);

        $collection = FileCollection::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'visibility' => $validated['visibility'],
            'created_by' => auth()->id(),
            'metadata' => $validated['metadata'] ?? []
        ]);

        if ($request->hasFile('files')) {
            $this->fileService->storeMany(
                $request->file('files'),
                "collections/{$collection->id}"
            );
        }

        return $this->successResponse(
            $collection->load(['creator', 'files']),
            201
        );
    }

    public function show(FileCollection $collection)
    {
        if (!auth()->user()->is_admin && $collection->visibility !== 'public') {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse(
            $collection->load(['creator', 'files'])
        );
    }

    public function update(Request $request, FileCollection $collection)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => 'string',
            'visibility' => 'in:' . implode(',', File::VISIBILITIES),
            'metadata' => 'nullable|array'
        ]);

        $collection->update($validated);

        if ($request->hasFile('files')) {
            $this->fileService->storeMany(
                $request->file('files'),
                "collections/{$collection->id}"
            );
        }

        return $this->successResponse($collection->load(['creator', 'files']));
    }

    public function destroy(FileCollection $collection)
    {
        foreach ($collection->files as $file) {
            $this->fileService->delete($file);
        }

        $collection->delete();
        return $this->successResponse(['message' => 'Collection deleted successfully']);
    }

    public function addFiles(Request $request, FileCollection $collection)
    {
        $request->validate([
            'files.*' => 'required|file|max:' . config('filesystems.max_upload_size', 10240)
        ]);

        $files = $this->fileService->storeMany(
            $request->file('files'),
            "collections/{$collection->id}"
        );

        return $this->successResponse($files);
    }

    public function removeFiles(Request $request, FileCollection $collection)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:files,id'
        ]);

        $files = $collection->files()->whereIn('id', $request->file_ids)->get();

        foreach ($files as $file) {
            $this->fileService->delete($file);
        }

        return $this->successResponse(['message' => 'Files removed successfully']);
    }
} 