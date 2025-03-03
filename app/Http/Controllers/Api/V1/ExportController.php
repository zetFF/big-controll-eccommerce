<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Export;
use App\Jobs\ProcessExport;
use App\Services\ExportImportService;
use Illuminate\Http\Request;

class ExportController extends BaseController
{
    public function __construct(
        private ExportImportService $exportService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $exports = Export::with('creator')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($exports);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'filters' => 'nullable|array',
            'columns' => 'required|array',
            'format' => 'required|in:' . implode(',', Export::FORMATS),
            'metadata' => 'nullable|array',
        ]);

        $export = $this->exportService->createExport($validated);
        ProcessExport::dispatch($export);

        return $this->successResponse($export, 201);
    }

    public function show(Export $export)
    {
        return $this->successResponse($export->load('creator'));
    }

    public function download(Export $export)
    {
        if ($export->status !== 'completed') {
            return $this->errorResponse('Export is not ready for download', 400);
        }

        return Storage::download(
            $export->file_path,
            $export->file_name ?? basename($export->file_path)
        );
    }

    public function destroy(Export $export)
    {
        if ($export->file_path && Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }
        
        $export->delete();
        return $this->successResponse(['message' => 'Export deleted successfully']);
    }
} 