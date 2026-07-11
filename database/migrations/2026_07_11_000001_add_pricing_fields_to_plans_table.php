<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the columns needed for the super-admin dashboard to fully drive the
     * public pricing page (/pricing), so that plans created in
     * /super-admin/plans show up there with their own copy, badge, and value
     * bullets instead of relying on hard-coded slugs in PricingController.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Bullet points shown on the public pricing card (e.g. ["نقاط بيع", "مخازن", ...])
            $table->json('value_props')->nullable()->after('features');

            // Optional uppercase eyebrow label shown above the plan name on the card
            // (e.g. "الباقة الأساسية"). Falls back to the plan name if null.
            $table->string('display_label')->nullable()->after('value_props');

            // Whether this card gets the "الأكثر طلباً" featured treatment.
            $table->boolean('is_featured')->default(false)->after('display_label');

            // Sort order on the pricing page (lower = earlier). Defaults to 0.
            $table->unsignedInteger('sort_order')->default(0)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['value_props', 'display_label', 'is_featured', 'sort_order']);
        });
    }
};