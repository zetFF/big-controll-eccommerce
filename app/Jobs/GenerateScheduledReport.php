<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportGenerationService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateScheduledReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Report $report
    ) {}

    public function handle(
        ReportGenerationService $reportService,
        NotificationService $notificationService
    ): void {
        try {
            $filePath = $reportService->generateReport($this->report);

            foreach ($this->report->recipients as $recipient) {
                $notificationService->send(
                    User::where('email', $recipient)->first(),
                    'report.generated',
                    [
                        'report_name' => $this->report->name,
                        'download_url' => Storage::url($filePath)
                    ],
                    ['email', 'database']
                );
            }
        } catch (\Exception $e) {
            \Log::error('Report generation failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 