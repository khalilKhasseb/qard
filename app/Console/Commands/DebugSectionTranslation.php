<?php

namespace App\Console\Commands;

use App\Models\BusinessCard;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class DebugSectionTranslation extends Command
{
    protected $signature = 'debug:section-translation {card_id} {target_language}';
    protected $description = 'Debug section title translation for a specific card';

    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle()
    {
        $cardId = $this->argument('card_id');
        $targetLanguage = $this->argument('target_language');
        
        $card = BusinessCard::with('sections')->find($cardId);
        
        if (!$card) {
            $this->error("Card not found with ID: {$cardId}");
            return 1;
        }
        
        $this->info("Card: {$card->id} - Primary Language: {$card->language->code}");
        $this->info("Target Language: {$targetLanguage}");
        $this->info("Total Sections: " . $card->sections->count());
        
        foreach ($card->sections as $section) {
            $this->info("\n--- Section {$section->id} ---");
            $this->info("Type: {$section->type}");
            $this->info("Order: {$section->order}");
            
            // Check current title
            $currentTitle = $section->title;
            $this->info("Current Title (Raw): " . json_encode($currentTitle));
            
            if (is_array($currentTitle) && isset($currentTitle[$targetLanguage])) {
                $this->info("Translation already exists: {$currentTitle[$targetLanguage]}");
            } else {
                $this->info("No translation found for language: {$targetLanguage}");
                
                // Get primary language title
                $primaryLang = $card->language->code;
                $primaryTitle = is_array($currentTitle) 
                    ? ($currentTitle[$primaryLang] ?? 'No title in primary language')
                    : ($currentTitle ?? 'No title');
                    
                $this->info("Primary Title ({$primaryLang}): {$primaryTitle}");
                
                // Try to translate
                try {
                    $this->info("Attempting translation...");
                    $translatedTitle = $this->translationService->translateTitle(
                        $primaryTitle, 
                        $primaryLang, 
                        $targetLanguage
                    );
                    
                    $this->info("Translation Result: {$translatedTitle}");
                } catch (\Exception $e) {
                    $this->error("Translation failed: " . $e->getMessage());
                }
            }
        }
        
        return 0;
    }
}