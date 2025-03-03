<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Product $product;
    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['stock' => 10]);
        $this->address = Address::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_create_order()
    {
        $this->actingAs($this->user);

        // Add item to cart first
        $this->postJson('/api/v1/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $orderData = [
            'shipping_address_id' => $this->address->id,
            'payment_method' => 'bank_transfer',
            'shipping_method' => 'regular',
            'notes' => 'Test order'
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'shipping_method' => $orderData['shipping_method'],
                    'payment_method' => $orderData['payment_method'],
                    'status' => 'pending'
                ]
            ]);

        // Check if stock was reduced
        $this->assertEquals(8, $this->product->fresh()->stock);
    }

    public function test_cannot_order_with_insufficient_stock()
    {
        $this->actingAs($this->user);

        // Try to order more than available stock
        $this->postJson('/api/v1/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 20
        ]);

        $orderData = [
            'shipping_address_id' => $this->address->id,
            'payment_method' => 'bank_transfer',
            'shipping_method' => 'regular',
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(422);
    }
} 