<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookService
{
    public function createWebhook(array $data): Webhook
    {
        return Webhook::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => $data['events'],
            'secret' => $this->generateSecret(),
            'headers' => $data['headers'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'retry_count' => $data['retry_count'] ?? 3,
        ]);
    }

    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::where('is_active', true)
            ->where(function ($query) use ($event) {
                $query->whereJsonContains('events', $event)
                    ->orWhereJsonContains('events', '*');
            })
            ->get();

        foreach ($webhooks as $webhook) {
            $this->scheduleDelivery($webhook, $event, $payload);
        }
    }

    public function deliver(WebhookDelivery $delivery): void
    {
        $webhook = $delivery->webhook;
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders($this->prepareHeaders($webhook))
                ->timeout($webhook->timeout)
                ->post($webhook->url, [
                    'id' => (string) Str::uuid(),
                    'event' => $delivery->event,
                    'created_at' => now()->toIso8601String(),
                    'data' => $delivery->payload,
                ]);

            $delivery->update([
                'status_code' => $response->status(),
                'response' => [
                    'headers' => $response->headers(),
                    'body' => $response->json() ?? $response->body(),
                ],
                'processing_time' => microtime(true) - $startTime,
                'delivered_at' => now(),
            ]);

        } catch (\Exception $e) {
            $delivery->update([
                'status_code' => 0,
                'error' => $e->getMessage(),
                'processing_time' => microtime(true) - $startTime,
            ]);

            if ($delivery->attempt < $webhook->retry_count) {
                $this->scheduleRetry($delivery);
            }
        }
    }

    private function scheduleDelivery(Webhook $webhook, string $event, array $payload): void
    {
        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'attempt' => 1,
            'scheduled_at' => now(),
        ]);

        dispatch(new \App\Jobs\ProcessWebhookDelivery($delivery));
    }

    private function scheduleRetry(WebhookDelivery $delivery): void
    {
        $nextAttempt = $delivery->replicate();
        $nextAttempt->attempt = $delivery->attempt + 1;
        $nextAttempt->scheduled_at = now()->addMinutes(
            pow(2, $delivery->attempt - 1)
        );
        $nextAttempt->save();

        dispatch(new \App\Jobs\ProcessWebhookDelivery($nextAttempt))
            ->delay($nextAttempt->scheduled_at);
    }

    private function prepareHeaders(Webhook $webhook): array
    {
        $headers = $webhook->headers ?? [];
        $headers['User-Agent'] = config('app.name') . ' Webhook';
        $headers['Content-Type'] = 'application/json';

        if ($webhook->secret) {
            $timestamp = time();
            $headers['X-Webhook-Timestamp'] = $timestamp;
            $headers['X-Webhook-Signature'] = $this->generateSignature(
                $webhook->secret,
                $timestamp
            );
        }

        return $headers;
    }

    private function generateSignature(string $secret, int $timestamp): string
    {
        return hash_hmac('sha256', $timestamp, $secret);
    }

    private function generateSecret(): string
    {
        return Str::random(32);
    }
} 