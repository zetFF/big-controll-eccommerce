<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('message');
            $table->string('code')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->json('trace')->nullable();
            $table->string('request_method')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
}; 