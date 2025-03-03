<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('values');
            $table->json('errors');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_failures');
    }
}; 