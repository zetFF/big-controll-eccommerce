<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Size, Color, Material
            $table->timestamps();
        });

        Schema::create('variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_type_id')->constrained()->onDelete('cascade');
            $table->string('value'); // e.g., Small, Red, Cotton
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->json('variant_values'); // Store variant value IDs
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('variant_values');
        Schema::dropIfExists('variant_types');
    }
}; 