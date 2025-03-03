<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_docs', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('spec');
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_docs');
    }
}; 