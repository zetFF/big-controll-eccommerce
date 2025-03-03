<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private string $baseUrl;
    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.payment.base_url');
        $this->secretKey = config('services.payment.secret_key');
    }

    public function createPayment(Order $order): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->post($this->baseUrl . '/v1/payments', [
                'amount' => $order->total_amount,
                'currency' => 'IDR',
                'order_id' => $order->order_number,
                'customer' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone,
                ],
                'success_redirect_url' => route('payment.success'),
                'failure_redirect_url' => route('payment.failure'),
                'callback_url' => route('webhook.payment'),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        $calculatedSignature = hash_hmac('sha256', $payload, $this->secretKey);
        return hash_equals($calculatedSignature, $signature);
    }
} 