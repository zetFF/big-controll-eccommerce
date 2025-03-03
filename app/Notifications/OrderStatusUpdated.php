<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject("Order #{$this->order->order_number} Status Updated")
            ->greeting("Hello {$notifiable->name}")
            ->line("Your order #{$this->order->order_number} has been updated to: " . ucfirst($this->order->status));

        if ($this->order->status === 'shipped') {
            $message->line("Tracking Number: {$this->order->shipping_tracking_number}");
        }

        $message->action('View Order', route('orders.show', $this->order))
            ->line('Thank you for shopping with us!');

        return $message;
    }
} 