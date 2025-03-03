<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Notifications\OrderStatusUpdated;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items'])
            ->latest()
            ->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'shipping_tracking_number' => 'nullable|string|required_if:status,shipped'
        ]);

        $oldStatus = $order->status;

        if ($validated['status'] === 'shipped' && isset($validated['shipping_tracking_number'])) {
            $order->markAsShipped($validated['shipping_tracking_number']);
        } elseif ($validated['status'] === 'delivered') {
            $order->markAsDelivered();
        } elseif ($validated['status'] === 'cancelled') {
            $order->cancel();
        } else {
            $order->update(['status' => $validated['status']]);
        }

        // Send notification only if status has changed
        if ($oldStatus !== $order->status) {
            $order->user->notify(new OrderStatusUpdated($order));
        }

        return back()->with('success', 'Order status updated successfully');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed'
        ]);

        if ($validated['payment_status'] === 'paid') {
            $order->markAsPaid();
        } else {
            $order->update(['payment_status' => $validated['payment_status']]);
        }

        return back()->with('success', 'Payment status updated successfully');
    }
} 