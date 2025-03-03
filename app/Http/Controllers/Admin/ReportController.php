<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\SalesReport;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $startDate = request('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $orders = Order::with(['user', 'items'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate(20);

        // Calculate totals
        $totals = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                SUM(tax_amount) as total_tax,
                SUM(shipping_cost) as total_shipping
            ')
            ->first();

        // Get payment method breakdown
        $paymentMethods = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        return view('admin.reports.sales', compact(
            'orders',
            'totals',
            'paymentMethods',
            'startDate',
            'endDate'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $fileName = 'sales-report-' . $request->start_date . '-to-' . $request->end_date . '.xlsx';

        return Excel::download(
            new SalesReport($request->start_date, $request->end_date),
            $fileName
        );
    }
} 