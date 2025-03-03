<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Http\Requests\OrderRequest;
use App\Jobs\ProcessOrder;
use App\Jobs\ExportOrders;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Traits\ApiResponse;
use App\Services\PaymentService;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $total = 0;
            $items = [];

            foreach ($cart as $productId => $item) {
                $product = Product::findOrFail($productId);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                // Update stock
                $product->decrement('stock', $item['quantity']);
            }

            // Calculate shipping and tax
            $shipping_cost = $validated['shipping_method'] === 'express' ? 50000 : 20000;
            $tax_amount = $total * 0.11; // 11% tax

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . Str::upper(Str::random(8)),
                'user_id' => auth()->id(),
                'total_amount' => $total + $shipping_cost + $tax_amount,
                'tax_amount' => $tax_amount,
                'shipping_cost' => $shipping_cost,
                'shipping_method' => $validated['shipping_method'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            $order->items()->createMany($items);

            // Clear cart
            Session::forget('cart');

            // Dispatch job to process order
            ProcessOrder::dispatch($order)->onQueue('orders');

            DB::commit();

            return $this->createdResponse(
                new OrderResource($order->load(['items.product', 'user']))
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'shipping_tracking_number' => 'required_if:status,shipped|nullable|string',
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        ExportOrders::dispatch(
            $request->date_from,
            $request->date_to,
            auth()->id()
        )->onQueue('exports');

        return $this->successResponse([
            'message' => 'Export has been queued and will be emailed to you when complete.'
        ]);
    }

    public function initiatePayment(Order $order)
    {
        try {
            $payment = $this->paymentService->createPayment($order);

            return $this->successResponse([
                'payment_url' => $payment['redirect_url'],
                'payment_id' => $payment['id']
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Payment initiation failed', 500);
        }
    }
} 