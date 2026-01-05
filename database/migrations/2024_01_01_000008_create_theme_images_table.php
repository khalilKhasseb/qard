<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_path');
            $table->enum('file_type', ['background', 'header', 'logo', 'favicon']);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type')->nullable();
            $table->timestamps();

            $table->index(['theme_id', 'file_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_images');
    }
};
