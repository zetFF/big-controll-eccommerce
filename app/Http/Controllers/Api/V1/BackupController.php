<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends BaseController
{
    public function __construct(
        private BackupService $backupService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $backups = Backup::with('creator')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($backups);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', Backup::TYPES),
            'options' => 'nullable|array',
        ]);

        $backup = $this->backupService->createBackup(
            $request->type,
            $request->options ?? []
        );

        return $this->successResponse($backup, 201);
    }

    public function show(Backup $backup)
    {
        return $this->successResponse($backup->load('creator'));
    }

    public function download(Backup $backup)
    {
        if ($backup->status !== 'completed') {
            return $this->errorResponse('Backup is not ready for download', 400);
        }

        return response()->download($backup->path, basename($backup->path));
    }

    public function restore(Backup $backup)
    {
        try {
            $this->backupService->restoreBackup($backup);
            return $this->successResponse(['message' => 'Backup restored successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to restore backup: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Backup $backup)
    {
        if (file_exists($backup->path)) {
            unlink($backup->path);
        }
        
        $backup->delete();

        return $this->successResponse(['message' => 'Backup deleted successfully']);
    }
} 