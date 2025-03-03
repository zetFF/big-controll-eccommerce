<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $backups = Backup::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        return view('admin.backups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:database,files',
            'directories' => 'nullable|array',
            'directories.*' => 'string'
        ]);

        try {
            if ($validated['type'] === 'database') {
                $backup = $this->backupService->createDatabaseBackup();
            } else {
                $backup = $this->backupService->createFilesBackup($validated['directories'] ?? []);
            }

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(Backup $backup)
    {
        return Storage::disk($backup->disk)->download($backup->path);
    }

    public function restore(Backup $backup)
    {
        try {
            $this->backupService->restore($backup);
            return back()->with('success', 'Backup restored successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function destroy(Backup $backup)
    {
        $backup->delete();
        return back()->with('success', 'Backup deleted successfully');
    }
} 