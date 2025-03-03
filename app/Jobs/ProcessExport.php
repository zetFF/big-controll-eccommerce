<?php

namespace App\Jobs;

use App\Models\Export;
use App\Services\ExportImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    public function __construct(
        private Export $export
    ) {}

    public function handle(ExportImportService $service): void
    {
        $service->processExport($this->export);
    }
} 