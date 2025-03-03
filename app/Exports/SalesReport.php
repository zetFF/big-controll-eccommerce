<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SalesReport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $this->endDate = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();
    }

    public function collection()
    {
        return Order::with(['user', 'items.product'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Date',
            'Customer',
            'Email',
            'Items',
            'Subtotal',
            'Shipping',
            'Tax',
            'Total',
            'Payment Method',
            'Payment Status',
            'Order Status'
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->user->name,
            $order->user->email,
            $order->items->count(),
            number_format($order->subtotal, 2),
            number_format($order->shipping_cost, 2),
            number_format($order->tax_amount, 2),
            number_format($order->total_amount, 2),
            $order->payment_method,
            $order->payment_status,
            $order->status
        ];
    }
} 