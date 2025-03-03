<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService
    ) {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index()
    {
        return $this->successResponse([
            'backups' => $this->backupService->getBackups()
        ]);
    }

    public function store()
    {
        try {
            $this->backupService->createBackup();
            return $this->successResponse(['message' => 'Backup process started']);
        } catch (\Exception $e) {
            return $this->errorResponse('Backup failed: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'disk' => 'required|string',
            'path' => 'required|string',
        ]);

        try {
            $this->backupService->deleteBackup($request->disk, $request->path);
            return $this->successResponse(['message' => 'Backup deleted successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Delete failed: ' . $e->getMessage(), 500);
        }
    }

    public function download(Request $request)
    {
        $request->validate([
            'disk' => 'required|string',
            'path' => 'required|string',
        ]);

        try {
            $url = $this->backupService->downloadBackup($request->disk, $request->path);
            return $this->successResponse(['download_url' => $url]);
        } catch (\Exception $e) {
            return $this->errorResponse('Download failed: ' . $e->getMessage(), 500);
        }
    }
} 