<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\ExportCompletedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $dateFrom,
        private string $dateTo,
        private int $userId
    ) {}

    public function handle(): void
    {
        $orders = Order::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->with(['user', 'items.product'])
            ->get();

        $csv = Writer::createFromString('');
        
        // Add headers
        $csv->insertOne([
            'Order Number',
            'Customer',
            'Total Amount',
            'Status',
            'Payment Status',
            'Created At'
        ]);

        // Add data
        foreach ($orders as $order) {
            $csv->insertOne([
                $order->order_number,
                $order->user->name,
                $order->total_amount,
                $order->status,
                $order->payment_status,
                $order->created_at->format('Y-m-d H:i:s')
            ]);
        }

        $filename = "orders-export-" . now()->format('Y-m-d-His') . ".csv";
        Storage::put("exports/{$filename}", $csv->toString());

        // Notify user
        $user = \App\Models\User::find($this->userId);
        $user->notify(new ExportCompletedNotification($filename));
    }
} 