<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Activity;
use App\Models\ErrorLog;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // Get statistics for today
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Orders statistics
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $monthlyOrders = Order::whereMonth('created_at', $today->month)->count();

        // Revenue statistics
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $todayRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_amount');
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', $today->month)
            ->sum('total_amount');

        // Products statistics
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 5)->count();

        // Users statistics
        $totalCustomers = User::where('is_admin', false)->count();
        $newCustomers = User::where('is_admin', false)
            ->whereDate('created_at', '>=', $startOfMonth)
            ->count();

        // Get monthly sales chart data
        $monthlySales = Order::where('payment_status', 'paid')
            ->whereYear('created_at', $today->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Get recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Get top selling products
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'todayOrders',
            'monthlyOrders',
            'totalRevenue',
            'todayRevenue',
            'monthlyRevenue',
            'totalProducts',
            'lowStockProducts',
            'totalCustomers',
            'newCustomers',
            'monthlySales',
            'recentOrders',
            'topProducts'
        ));
    }

    public function analytics()
    {
        $period = request('period', '7d');
        $metrics = $this->analyticsService->getMetrics($period);
        
        return view('admin.analytics', compact('metrics', 'period'));
    }
} 