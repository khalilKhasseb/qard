<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\CardSection;
use App\Models\Language;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get default language
        $defaultLanguage = Language::where('is_active', true)->first();
        $defaultLang = $defaultLanguage ? $defaultLanguage->code : 'en';

        // First, ensure all titles are converted to JSON format if they aren't already
        $sections = CardSection::all();
        foreach ($sections as $section) {

//            if ($section->title && !empty(trim($section->title))) {

                $title = $section->title;

//                if (!str_starts_with(trim($title), '{')) {
                    $section->update([
                        'title' => json_encode([$defaultLang => $title]),
                    ]);
//                }
//            }
        }

        // Change column type to json

        Schema::table('card_sections', function (Blueprint $table) {
            $table->json('title')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON titles back to string format
        $sections = CardSection::all();
        foreach ($sections as $section) {
            if ($section->title) {
                $titleData = json_decode($section->title, true);

                $title = is_array($titleData) ? (reset($titleData) ?: '') : $section->title;
                $section->update([
                    'title' => $title,
                ]);
            }
        }

        // Change column type back to string
        Schema::table('card_sections', function (Blueprint $table) {
            $table->string('title')->change();
        });
    }
};
