<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $settings = $this->settingsService->all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable'
        ]);

        $success = $this->settingsService->updateMany($validated['settings']);

        return redirect()->route('admin.settings.index')
            ->with($success ? 'success' : 'error',
                $success ? 'Settings updated successfully' : 'Failed to update settings');
    }

    public function export()
    {
        $json = $this->settingsService->export();
        $filename = 'settings-' . now()->format('Y-m-d-His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048'
        ]);

        $json = file_get_contents($request->file('file')->path());
        $success = $this->settingsService->import($json);

        return redirect()->route('admin.settings.index')
            ->with($success ? 'success' : 'error',
                $success ? 'Settings imported successfully' : 'Failed to import settings');
    }
} 