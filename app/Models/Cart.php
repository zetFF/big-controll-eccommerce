<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2'
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updateTotal()
    {
        $this->total = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $this->save();
    }

    public function addItem($product, $quantity = 1, $attributes = [])
    {
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('attributes', json_encode($attributes))
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
        } else {
            $this->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->current_price,
                'attributes' => $attributes
            ]);
        }

        $this->updateTotal();
    }

    public function updateItem($itemId, $quantity)
    {
        $item = $this->items()->findOrFail($itemId);
        
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        $this->updateTotal();
    }

    public function removeItem($itemId)
    {
        $this->items()->findOrFail($itemId)->delete();
        $this->updateTotal();
    }

    public function clear()
    {
        $this->items()->delete();
        $this->updateTotal();
    }

    public function itemCount()
    {
        return $this->items->sum('quantity');
    }
} 