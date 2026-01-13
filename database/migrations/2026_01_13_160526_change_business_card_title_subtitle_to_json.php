<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure all titles are converted to JSON format if they aren't already
        // This is a bit tricky with raw SQL across different DBs, so we do it in PHP
        $cards = DB::table('business_cards')->get();
        $defaultLang = DB::table('languages')->where('is_default', true)->value('code') ?? 'en';

        foreach ($cards as $card) {
            $title = $card->title;
            // Check if it's already JSON (starts with {)
            if (!str_starts_with(trim($title), '{')) {
                DB::table('business_cards')->where('id', $card->id)->update([
                    'title' => json_encode([$defaultLang => $title]),
                    'subtitle' => $card->subtitle ? json_encode([$defaultLang => $card->subtitle]) : null,
                ]);
            }
        }

        Schema::table('business_cards', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('subtitle')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To reverse, we'd need to pick one language to keep as the string
        $cards = DB::table('business_cards')->get();
        foreach ($cards as $card) {
            $titleData = json_decode($card->title, true);
            $subtitleData = $card->subtitle ? json_decode($card->subtitle, true) : null;

            $title = is_array($titleData) ? (reset($titleData) ?: '') : $card->title;
            $subtitle = is_array($subtitleData) ? (reset($subtitleData) ?: null) : $card->subtitle;

            DB::table('business_cards')->where('id', $card->id)->update([
                'title' => $title,
                'subtitle' => $subtitle,
            ]);
        }

        Schema::table('business_cards', function (Blueprint $table) {
            $table->string('title')->change();
            $table->string('subtitle')->nullable()->change();
        });
    }
};
