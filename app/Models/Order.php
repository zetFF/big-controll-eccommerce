<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'tax_amount',
        'shipping_cost',
        'shipping_method',
        'shipping_tracking_number',
        'status',
        'notes',
        'payment_method',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->total_amount - ($this->tax_amount + $this->shipping_cost);
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest()->first();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => 'processing'
        ]);
    }

    public function markAsShipped($trackingNumber)
    {
        $this->update([
            'status' => 'shipped',
            'shipping_tracking_number' => $trackingNumber
        ]);
    }

    public function markAsDelivered()
    {
        $this->update(['status' => 'delivered']);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
} 