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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('translation_credits_monthly')->default(0)->after('features');
            $table->boolean('unlimited_translations')->default(false)->after('translation_credits_monthly');
            $table->decimal('per_credit_cost', 8, 6)->default(0)->after('unlimited_translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['translation_credits_monthly', 'unlimited_translations', 'per_credit_cost']);
        });
    }
};
