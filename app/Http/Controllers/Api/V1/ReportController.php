<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Report;
use App\Services\ReportGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends BaseController
{
    public function __construct(
        private ReportGenerationService $reportService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $reports = Report::latest()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($reports);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', Report::TYPES),
            'parameters' => 'required|array',
            'schedule' => 'nullable|array',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
        ]);

        $report = Report::create($validated);

        if (!empty($validated['schedule'])) {
            // Schedule the report generation
            $this->scheduleReport($report);
        }

        return $this->successResponse($report, 201);
    }

    public function show(Report $report)
    {
        return $this->successResponse($report);
    }

    public function generate(Report $report)
    {
        try {
            $filePath = $this->reportService->generateReport($report);
            return $this->successResponse([
                'message' => 'Report generated successfully',
                'download_url' => Storage::url($filePath)
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Report generation failed: ' . $e->getMessage(), 500);
        }
    }

    public function download(Report $report)
    {
        if (!$report->file_path || !Storage::exists($report->file_path)) {
            return $this->errorResponse('Report file not found', 404);
        }

        return Storage::download($report->file_path);
    }

    private function scheduleReport(Report $report): void
    {
        // Implementation of report scheduling logic
    }
} 