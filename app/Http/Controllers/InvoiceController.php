<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use PDF;

class InvoiceController extends Controller
{
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        return view('invoices.show', compact('order'));
    }

    public function download(Order $order)
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        $pdf = PDF::loadView('invoices.pdf', compact('order'));
        
        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
} 