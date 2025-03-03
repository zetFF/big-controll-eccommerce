<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderProcessingNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Order $order
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order is Being Processed')
            ->line('Your order #' . $this->order->order_number . ' is now being processed.')
            ->line('Total Amount: ' . number_format($this->order->total_amount, 2))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for shopping with us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Your order is being processed'
        ];
    }
} 