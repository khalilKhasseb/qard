<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('language', ['en', 'ar'])->default('en')->after('password');
            $table->enum('subscription_tier', ['free', 'pro', 'business'])->default('free')->after('language');
            $table->enum('subscription_status', ['active', 'pending', 'canceled', 'expired'])->default('pending')->after('subscription_tier');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
            $table->timestamp('last_login')->nullable()->after('subscription_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'language',
                'subscription_tier',
                'subscription_status',
                'subscription_expires_at',
                'last_login',
            ]);
        });
    }
};
