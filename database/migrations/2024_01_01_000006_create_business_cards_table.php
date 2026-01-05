<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->json('theme_overrides')->nullable();
            $table->string('custom_slug')->nullable()->unique();
            $table->string('share_url')->unique();
            $table->string('qr_code_url')->nullable();
            $table->string('nfc_identifier')->nullable()->unique();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_published']);
            $table->index('custom_slug');
            $table->index('nfc_identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_cards');
    }
};
