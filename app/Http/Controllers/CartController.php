<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected function getOrCreateCart()
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $cart = Cart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId)
                  ->orWhere('user_id', $userId);
        })->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'user_id' => $userId
            ]);
        }

        return $cart;
    }

    public function index()
    {
        $cart = $this->getOrCreateCart();
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'attributes' => 'nullable|array'
        ]);

        if (!$product->inStock()) {
            return back()->with('error', 'Product is out of stock');
        }

        $cart = $this->getOrCreateCart();
        $cart->addItem($product, $validated['quantity'], $validated['attributes'] ?? []);

        return back()->with('success', 'Product added to cart');
    }

    public function update(Request $request, $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = $this->getOrCreateCart();
        $cart->updateItem($itemId, $validated['quantity']);

        return back()->with('success', 'Cart updated');
    }

    public function remove($itemId)
    {
        $cart = $this->getOrCreateCart();
        $cart->removeItem($itemId);

        return back()->with('success', 'Item removed from cart');
    }

    public function clear()
    {
        $cart = $this->getOrCreateCart();
        $cart->clear();

        return back()->with('success', 'Cart cleared');
    }

    public function addToCart(Request $request, $type, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id'
        ]);

        $cart = session()->get('cart', []);
        $quantity = $validated['quantity'];

        switch ($type) {
            case 'product':
                $product = Product::findOrFail($id);
                $variant = null;

                if ($request->filled('variant_id')) {
                    $variant = $product->variants()->findOrFail($validated['variant_id']);
                    $stock = $variant->stock;
                    $price = $variant->price;
                    $cartKey = "product_{$product->id}_variant_{$variant->id}";
                } else {
                    $stock = $product->stock;
                    $price = $product->price;
                    $cartKey = "product_{$product->id}";
                }

                if ($stock < $quantity) {
                    return back()->with('error', 'Not enough stock available.');
                }

                if (isset($cart[$cartKey])) {
                    $cart[$cartKey]['quantity'] += $quantity;
                } else {
                    $cart[$cartKey] = [
                        'type' => 'product',
                        'id' => $product->id,
                        'variant_id' => $variant ? $variant->id : null,
                        'name' => $product->name,
                        'price' => $price,
                        'quantity' => $quantity,
                        'image' => $product->images->first()?->image_path
                    ];
                }
                break;

            case 'bundle':
                $bundle = ProductBundle::findOrFail($id);
                $cartKey = "bundle_{$bundle->id}";

                // Check stock for all products in bundle
                foreach ($bundle->products as $product) {
                    $requiredQuantity = $product->pivot->quantity * $quantity;
                    if ($product->stock < $requiredQuantity) {
                        return back()->with('error', "Not enough stock for {$product->name} in bundle.");
                    }
                }

                if (isset($cart[$cartKey])) {
                    $cart[$cartKey]['quantity'] += $quantity;
                } else {
                    $cart[$cartKey] = [
                        'type' => 'bundle',
                        'id' => $bundle->id,
                        'name' => $bundle->name,
                        'price' => $bundle->price,
                        'quantity' => $quantity,
                        'products' => $bundle->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'quantity' => $product->pivot->quantity
                            ];
                        })->toArray()
                    ];
                }
                break;
        }

        session()->put('cart', $cart);
        return back()->with('success', 'Item added to cart successfully.');
    }
} 