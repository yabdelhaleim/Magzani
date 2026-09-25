<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 20)->nullable()->after('password');
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('phone');
                    $table->index('is_active');
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('admin')->after('is_active');
                    $table->index('role');
                }
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
