<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries;
    public $backoff = 30;

    public function __construct(
        private Webhook $webhook,
        private string $event,
        private array $payload
    ) {
        $this->tries = $webhook->retry_count;
    }

    public function handle(WebhookService $webhookService): void
    {
        if (!$webhookService->sendWebhook($this->webhook, $this->event, $this->payload)) {
            throw new \Exception('Webhook delivery failed');
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Webhook job failed', [
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'error' => $exception->getMessage(),
        ]);
    }
} 