<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderProcessingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Order $order
    ) {}

    public function handle(): void
    {
        // Update stock
        foreach ($this->order->items as $item) {
            $product = $item->product;
            $product->decrement('stock', $item->quantity);
        }

        // Update order status
        $this->order->update(['status' => 'processing']);

        // Send notification to user
        $this->order->user->notify(new OrderProcessingNotification($this->order));
    }
} 