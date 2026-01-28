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
        Schema::create('translation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_card_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('translatable'); // polymorphic (CardSection, etc.)

            // Translation details
            $table->string('source_language', 10);
            $table->string('target_language', 10);
            $table->text('source_text');
            $table->text('translated_text');
            $table->string('translation_method')->default('auto'); // 'auto', 'manual', 'ai_improve'
            $table->string('provider')->nullable(); // 'openrouter', 'google', etc.
            $table->string('model')->nullable(); // e.g., 'google/gemini-2.0-flash-exp:free'

            // Quality & verification
            $table->integer('quality_score')->nullable(); // 0-100
            $table->enum('verification_status', ['pending', 'auto_verified', 'approved', 'rejected', 'needs_review'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Usage tracking
            $table->integer('character_count')->default(0);
            $table->integer('credits_used')->default(1);
            $table->decimal('cost', 8, 6)->default(0);

            // Metadata
            $table->json('metadata')->nullable(); // API response, confidence scores, etc.
            $table->timestamps();
            $table->softDeletes(); // For audit purposes

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['business_card_id', 'source_language', 'target_language'], 'th_card_langs_idx');
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_history');
    }
};
