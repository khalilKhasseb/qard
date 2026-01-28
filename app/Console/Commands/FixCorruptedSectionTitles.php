<?php

namespace App\Console\Commands;

use App\Models\CardSection;
use Illuminate\Console\Command;

class FixCorruptedSectionTitles extends Command
{
    protected $signature = 'fix:section-titles';

    protected $description = 'Fix corrupted section titles that have wrong language mappings';

    public function handle()
    {
        $this->info('Fixing corrupted section titles...');

        $sections = CardSection::whereNotNull('title')->get();
        $fixed = 0;

        foreach ($sections as $section) {
            $title = $section->title;
            $this->info("Section {$section->id}: ".json_encode($title));
            $changed = false;

            if (is_array($title)) {
                // Check if English title contains Arabic characters (corrupted)
                if (isset($title['en']) && $this->containsArabic($title['en'])) {
                    $this->warn("  → English title contains Arabic: {$title['en']}");

                    // If there's no Arabic title, move the Arabic text to Arabic field
                    if (! isset($title['ar'])) {
                        $title['ar'] = $title['en'];
                        $title['en'] = 'Untitled'; // Set a placeholder English title
                        $changed = true;
                        $this->info("  → Moved Arabic text to 'ar' field, set placeholder English title");
                    } else {
                        // If Arabic field exists, just clear the corrupted English field
                        $title['en'] = 'Untitled';
                        $changed = true;
                        $this->info('  → Cleared corrupted English field (Arabic field already exists)');
                    }
                }

                // Check if Arabic title contains English characters (corrupted)
                if (isset($title['ar']) && ! $this->containsArabic($title['ar']) && strlen($title['ar']) > 0) {
                    $this->warn("  → Arabic title contains English: {$title['ar']}");
                    // This might be a misplaced English title, but we'll leave it for manual review
                }

                if ($changed) {
                    $section->title = $title;
                    $section->save();
                    $fixed++;
                    $this->info('  → Updated to: '.json_encode($title));
                }
            }
        }

        $this->info("Fixed {$fixed} corrupted section titles.");

        return 0;
    }

    private function containsArabic($text)
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }
}
