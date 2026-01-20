<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_card_id')->constrained()->cascadeOnDelete();
            $table->enum('section_type', [
                'contact',
                'social',
                'services',
                'products',
                'testimonials',
                'hours',
                'appointments',
                'gallery',
                'video',
                'links',
                'about',
                'custom',
            ]);
            $table->string('title');
            $table->json('content');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['business_card_id', 'sort_order']);
            $table->index(['business_card_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_sections');
    }
};
