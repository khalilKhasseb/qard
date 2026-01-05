<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_system_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->json('config');
            $table->string('preview_image')->nullable();
            $table->integer('used_by_cards_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_public']);
            $table->index('is_system_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
