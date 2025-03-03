<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    public function getSalesReport(string $startDate, string $endDate): array
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy('date')
            ->get();

        return [
            'daily_sales' => $orders,
            'total_orders' => $orders->sum('total_orders'),
            'total_revenue' => $orders->sum('total_sales'),
            'average_order_value' => $orders->avg('total_sales'),
        ];
    }

    public function getTopProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getCustomerStats(string $startDate, string $endDate): array
    {
        $stats = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.payment_status', 'paid')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_spent')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_spent', 'desc')
            ->get();

        return [
            'top_customers' => $stats->take(10),
            'total_customers' => $stats->count(),
            'average_customer_value' => $stats->avg('total_spent'),
        ];
    }
} 