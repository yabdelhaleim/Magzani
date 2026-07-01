<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->comment('الكاشير الذي فتح الوردية');

            $table->timestamp('opened_at')
                  ->comment('وقت فتح الوردية');

            $table->timestamp('closed_at')
                  ->nullable()
                  ->comment('وقت إغلاق الوردية');

            $table->decimal('opening_balance', 15, 2)
                  ->default(0)
                  ->comment('رصيد الصندوق عند الفتح');

            $table->decimal('closing_balance_actual', 15, 2)
                  ->nullable()
                  ->comment('الرصيد الفعلي عند الإغلاق (يُدخله الكاشير)');

            $table->decimal('closing_balance_expected', 15, 2)
                  ->nullable()
                  ->comment('الرصيد المتوقع رياضياً');

            $table->decimal('difference', 15, 2)
                  ->nullable()
                  ->comment('الفرق بين الفعلي والمتوقع');

            $table->decimal('total_sales', 15, 2)
                  ->default(0)
                  ->comment('إجمالي مبيعات الوردية');

            $table->decimal('total_returns', 15, 2)
                  ->default(0)
                  ->comment('إجمالي المرتجعات في الوردية');

            $table->integer('sales_count')
                  ->default(0)
                  ->comment('عدد فواتير البيع');

            $table->integer('returns_count')
                  ->default(0)
                  ->comment('عدد فواتير المرتجعات');

            $table->enum('status', ['open', 'closed'])
                  ->default('open')
                  ->comment('حالة الوردية');

            $table->text('notes')
                  ->nullable()
                  ->comment('ملاحظات عند إغلاق الوردية');

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('opened_at');
            $table->index('status');
        });

        // Add shift_id and source columns to sales_invoices
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('shift_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('pos_shifts')
                  ->onDelete('set null')
                  ->comment('الوردية المرتبطة');

            $table->string('source', 20)
                  ->default('sales')
                  ->after('shift_id')
                  ->comment('مصدر الفاتورة: sales أو pos');

            $table->index('source');
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'source']);
        });

        Schema::dropIfExists('pos_shifts');
    }
};
