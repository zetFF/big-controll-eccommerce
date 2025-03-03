<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_can_get_all_products()
    {
        Product::factory(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'price',
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_product()
    {
        $category = Category::factory()->create();
        
        $productData = [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(1, 100),
            'category_ids' => [$category->id],
            'images' => [
                UploadedFile::fake()->image('product1.jpg'),
                UploadedFile::fake()->image('product2.jpg'),
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
    }

    public function test_normal_user_cannot_create_product()
    {
        $productData = [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(1, 100),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(403);
    }
} 