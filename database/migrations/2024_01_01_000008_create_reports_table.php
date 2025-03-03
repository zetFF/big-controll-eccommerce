<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->json('parameters');
            $table->json('schedule')->nullable();
            $table->json('recipients');
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}; 