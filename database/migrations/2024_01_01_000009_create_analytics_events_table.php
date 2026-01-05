<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_card_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('card_section_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('event_type', [
                'view',
                'nfc_tap',
                'qr_scan',
                'social_share',
                'section_click',
                'contact_save',
                'link_click'
            ]);
            $table->string('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['business_card_id', 'event_type']);
            $table->index(['business_card_id', 'created_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
