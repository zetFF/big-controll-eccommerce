<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())
                   ->orWhere('session_id', session()->getId())
                   ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', compact('cart'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $cart = Cart::where('user_id', auth()->id())
                   ->orWhere('session_id', session()->getId())
                   ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => auth()->id(),
                'total_amount' => $cart->total,
                'tax_amount' => $cart->total * 0.1, // 10% tax
                'shipping_cost' => 10.00, // Fixed shipping cost for now
                'shipping_method' => $validated['shipping_method'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes']
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'metadata' => $item->attributes
                ]);

                // Update product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear the cart
            $cart->clear();

            DB::commit();

            // Redirect to payment gateway or order confirmation
            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order placed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
} 