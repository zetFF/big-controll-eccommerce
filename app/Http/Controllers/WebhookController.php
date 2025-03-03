<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use App\Events\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function handlePayment(Request $request)
    {
        // Verify webhook signature
        if (!$this->paymentService->verifyWebhookSignature(
            $request->header('X-Signature'),
            $request->getContent()
        )) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $payload = $request->all();
        
        try {
            $order = Order::where('order_number', $payload['order_id'])->firstOrFail();
            
            if ($payload['status'] === 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Broadcast payment received event
                event(new PaymentReceived($order));
            }

            return response()->json(['message' => 'Webhook processed']);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());
            return response()->json(['message' => 'Processing failed'], 500);
        }
    }
} 