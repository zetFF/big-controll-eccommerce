<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\ExportImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    public function __construct(
        private Import $import
    ) {}

    public function handle(ExportImportService $service): void
    {
        $service->processImport($this->import);
    }
} 