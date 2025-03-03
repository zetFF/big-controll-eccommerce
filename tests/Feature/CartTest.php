<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Product::factory()->create(['stock' => 5]);
    }

    public function test_can_add_to_cart()
    {
        $response = $this->postJson('/api/v1/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200);

        $cartResponse = $this->getJson('/api/v1/cart');
        $cartResponse->assertStatus(200)
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'quantity',
                        'subtotal'
                    ]
                ],
                'total'
            ]);
    }

    public function test_cannot_add_more_than_stock()
    {
        $response = $this->postJson('/api/v1/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 10
        ]);

        $response->assertStatus(422);
    }
} 