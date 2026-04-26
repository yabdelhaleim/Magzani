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
        // تحديث جدول الأدوار
        if (!Schema::hasColumn('roles', 'color')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('color')->nullable()->after('description');
            });
        }

        if (!Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('color');
            });
        }

        // تحديث جدول الصلاحيات
        if (!Schema::hasColumn('permissions', 'module')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('module')->nullable()->after('description');
            });
        }

        if (!Schema::hasColumn('permissions', 'action')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('action')->nullable()->after('module');
            });
        }

        if (!Schema::hasColumn('permissions', 'is_system')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('action');
            });
        }

        // إنشاء جدول العلاقة بين الصلاحيات والمستخدمين إذا لم يكن موجوداً
        if (!Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'permission_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // يمكن إزالة الأعمدة إذا لزم الأمر
    }
};
