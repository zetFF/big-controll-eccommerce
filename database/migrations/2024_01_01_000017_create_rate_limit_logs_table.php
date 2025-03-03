<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_limit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_limit_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->integer('requests');
            $table->boolean('blocked');
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_limit_logs');
    }
}; 