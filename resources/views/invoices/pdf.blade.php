<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 40px;
        }
        .header {
            margin-bottom: 40px;
        }
        .header:after {
            content: "";
            display: table;
            clear: both;
        }
        .company-info {
            float: left;
        }
        .invoice-info {
            float: right;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .totals {
            width: 300px;
            float: right;
            margin-bottom: 40px;
        }
        .total-row {
            padding: 8px 0;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            margin-top: 8px;
            padding-top: 8px;
        }
        .footer {
            text-align: center;
            color: #666;
            padding-top: 40px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ config('app.name') }}</div>
                <div>123 Business Street</div>
                <div>City, Country 12345</div>
                <div>contact@example.com</div>
            </div>
            <div class="invoice-info">
                <h2>Invoice #{{ $order->order_number }}</h2>
                <div>Date: {{ $order->created_at->format('M d, Y') }}</div>
                <div>
                    <strong>Bill To:</strong><br>
                    {{ $order->user->name }}<br>
                    {{ $order->user->email }}
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Quantity</th>
                    <th class="amount">Unit Price</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->product_name }}
                            @if($item->metadata)
                                <br>
                                <small>
                                    @foreach($item->metadata as $key => $value)
                                        {{ ucfirst($key) }}: {{ $value }}
                                    @endforeach
                                </small>
                            @endif
                        </td>
                        <td class="amount">{{ $item->quantity }}</td>
                        <td class="amount">{{ number_format($item->price, 2) }}</td>
                        <td class="amount">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <div style="display: flex; justify-content: space-between;">
                    <span>Subtotal:</span>
                    <span>{{ number_format($order->subtotal, 2) }}</span>
                </div>
            </div>
            <div class="total-row">
                <div style="display: flex; justify-content: space-between;">
                    <span>Shipping:</span>
                    <span>{{ number_format($order->shipping_cost, 2) }}</span>
                </div>
            </div>
            <div class="total-row">
                <div style="display: flex; justify-content: space-between;">
                    <span>Tax:</span>
                    <span>{{ number_format($order->tax_amount, 2) }}</span>
                </div>
            </div>
            <div class="total-row final">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total:</span>
                    <span>{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Payment Method: {{ ucfirst($order->payment_method) }}</p>
            <p>Payment Status: {{ ucfirst($order->payment_status) }}</p>
            @if($order->notes)
                <p>Notes: {{ $order->notes }}</p>
            @endif
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html> 