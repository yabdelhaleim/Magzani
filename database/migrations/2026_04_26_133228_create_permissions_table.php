<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // جدول الأدوار
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('color')->nullable(); // لون عرض الواجهة
            $table->boolean('is_system')->default(false); // أدوار النظام لا يمكن حذفها
            $table->timestamps();
        });

        // جدول الصلاحيات
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('module')->nullable(); // الموديول (sales, purchases, warehouse, etc.)
            $table->string('action')->nullable(); // الإجراء (create, read, update, delete, etc.)
            $table->boolean('is_system')->default(false); // صلاحيات النظام لا يمكن حذفها
            $table->timestamps();
        });

        // جدول العلاقة بين الصلاحيات والأدوار
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();
        });

        // جدول العلاقة بين الصلاحيات والمستخدمين
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // جدول العلاقة بين الأدوار والمستخدمين
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // فهرس للأداء السريع
        Schema::table('roles')->index('name');
        Schema::table('permissions')->index(['module', 'action']);
        Schema::table('permission_user')->index(['user_id', 'permission_id']);
        Schema::table('role_user')->index(['user_id', 'role_id']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
