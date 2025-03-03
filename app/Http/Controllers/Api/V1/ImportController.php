<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Import;
use App\Jobs\ProcessImport;
use App\Services\ExportImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends BaseController
{
    public function __construct(
        private ExportImportService $importService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $imports = Import::with(['creator', 'failures'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($imports);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'file' => 'required|file|mimes:csv,xlsx',
            'mapping' => 'required|array',
            'metadata' => 'nullable|array',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        $import = $this->importService->createImport([
            'name' => $request->name,
            'type' => $request->type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mapping' => $request->mapping,
            'metadata' => $request->metadata,
        ]);

        ProcessImport::dispatch($import);

        return $this->successResponse($import, 201);
    }

    public function show(Import $import)
    {
        return $this->successResponse($import->load(['creator', 'failures']));
    }

    public function failures(Import $import)
    {
        $failures = $import->failures()
            ->latest()
            ->paginate(request()->per_page ?? 15);

        return $this->successResponse($failures);
    }

    public function destroy(Import $import)
    {
        if ($import->file_path && Storage::exists($import->file_path)) {
            Storage::delete($import->file_path);
        }
        
        $import->delete();
        return $this->successResponse(['message' => 'Import deleted successfully']);
    }
} 