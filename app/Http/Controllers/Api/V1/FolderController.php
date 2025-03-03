<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Folder;
use App\Services\FileManagementService;
use Illuminate\Http\Request;

class FolderController extends BaseController
{
    public function __construct(
        private FileManagementService $fileService
    ) {
        parent::__construct();
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Folder::with('parent')
            ->when($request->parent_id, function ($q) use ($request) {
                return $q->where('parent_id', $request->parent_id);
            })
            ->latest();

        return $this->successResponse(
            $query->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'metadata' => 'nullable|array',
        ]);

        $parent = $request->parent_id 
            ? Folder::findOrFail($request->parent_id)
            : null;

        $folder = $this->fileService->createFolder(
            $request->name,
            $parent,
            $request->metadata ?? []
        );

        return $this->successResponse($folder, 201);
    }

    public function show(Folder $folder)
    {
        return $this->successResponse(
            $folder->load(['parent', 'children', 'files'])
        );
    }

    public function move(Request $request, Folder $folder)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $newParent = $request->parent_id 
            ? Folder::findOrFail($request->parent_id)
            : null;

        $folder = $this->fileService->moveFolder($folder, $newParent);

        return $this->successResponse($folder);
    }

    public function destroy(Folder $folder)
    {
        $this->fileService->deleteFolder($folder);
        return $this->successResponse(['message' => 'Folder deleted successfully']);
    }
} 