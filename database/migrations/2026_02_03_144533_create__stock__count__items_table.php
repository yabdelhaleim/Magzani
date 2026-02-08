<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            
            // العلاقات
            $table->unsignedBigInteger('stock_count_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            
            // الكميات
            $table->decimal('system_quantity', 15, 3)->comment('الكمية في النظام');
            $table->decimal('actual_quantity', 15, 3)->nullable()->comment('الكمية الفعلية');
            $table->decimal('variance', 15, 3)->default(0)->comment('الفرق');
            
            // حالة العنصر (مع كل الحالات)
            $table->enum('status', [
                'pending',   // لم يتم الجرد
                'counted',   // تم الجرد
                'adjusted',  // تم التسوية
                'skipped'    // تم التخطي
            ])->default('pending')->index()->comment('حالة العنصر في الجرد');
            
            // حقول الموافقة على التعديل
            $table->boolean('adjustment_approved')
                  ->default(false)
                  ->index()
                  ->comment('هل تمت الموافقة على التعديل؟');
            
            $table->text('approval_notes')
                  ->nullable()
                  ->comment('ملاحظات الموافقة/الرفض');
            
            $table->unsignedBigInteger('approved_by')
                  ->nullable()
                  ->index()
                  ->comment('المستخدم الذي وافق/رفض');
            
            $table->dateTime('approved_at')
                  ->nullable()
                  ->index()
                  ->comment('تاريخ الموافقة/الرفض');
            
            // ملاحظات
            $table->text('notes')->nullable();
            
            // من قام بالجرد
            $table->unsignedBigInteger('counted_by')->nullable()->index();
            $table->dateTime('counted_at')->nullable()->index();
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['stock_count_id', 'product_id'], 'unique_stock_count_product');
            
            // Composite Indexes للـ queries المتكررة
            $table->index(['stock_count_id', 'status']);
            $table->index(['stock_count_id', 'adjustment_approved']);
            $table->index(['product_id', 'status']);
            $table->index(['counted_by', 'counted_at']);
            $table->index(['approved_by', 'approved_at']);
            $table->index('created_at');
            
            // Foreign Keys
            $table->foreign('stock_count_id')
                  ->references('id')->on('stock_counts')
                  ->onDelete('cascade');
                  
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade');
                  
            $table->foreign('counted_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('approved_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
        
        // تحسين الـ table engine
        DB::statement('ALTER TABLE stock_count_items ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};