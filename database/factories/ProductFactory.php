<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'discount_price' => $this->faker->optional()->randomFloat(2, 5, 900),
            'stock' => $this->faker->numberBetween(0, 100),
            'sku' => 'PRD-' . strtoupper(Str::random(8)),
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'metadata' => null,
        ];
    }
} 