<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom baru setelah email
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'user'])->default('user')->after('phone');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('two_factor_secret')->nullable()->after('remember_token');
            $table->string('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->softDeletes(); // Menambahkan soft delete
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'is_active',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'deleted_at'
            ]);
        });
    }
}; 