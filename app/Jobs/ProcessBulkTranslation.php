<?php

namespace App\Jobs;

use App\Models\BusinessCard;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessBulkTranslation implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $cardId,
        public array $targetLanguages,
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translationService): void
    {
        $user = User::find($this->userId);
        $card = BusinessCard::find($this->cardId);

        if (! $user || ! $card) {
            Log::error('Bulk translation failed - user or card not found', [
                'user_id' => $this->userId,
                'card_id' => $this->cardId,
            ]);

            return;
        }

        $results = [
            'card_id' => $this->cardId,
            'user_id' => $this->userId,
            'languages' => [],
        ];

        $totalLangs = count($this->targetLanguages);
        $processedLangs = 0;

        foreach ($this->targetLanguages as $targetLang) {
            try {
                $onProgress = function ($completed, $total) use ($targetLang, $processedLangs, $totalLangs) {
                    // Global progress calculation: (processedLangs / totalLangs) + (currentLangProgress / totalLangs)
                    $currentLangProgress = $completed / $total;
                    $globalPercentage = round((($processedLangs + $currentLangProgress) / $totalLangs) * 100);

                    Cache::put("translation_progress:{$this->cardId}:{$this->userId}", [
                        'percentage' => $globalPercentage,
                        'completed' => $processedLangs,
                        'current_completed' => $completed,
                        'current_total' => $total,
                        'total_langs' => $totalLangs,
                        'current_lang' => $targetLang,
                        'timestamp' => now()->toISOString(),
                    ], now()->addMinutes(10));
                };

                $result = $translationService->translateBusinessCard($card, $targetLang, $user, [
                    'on_progress' => $onProgress,
                ]);

                $processedLangs++;

                $results['languages'][$targetLang] = [
                    'success' => true,
                    'sections_translated' => $result['results']['sections_translated'] ?? 0,
                ];

                Log::info('Card translated to language', [
                    'card_id' => $this->cardId,
                    'target_lang' => $targetLang,
                    'sections' => $result['results']['sections_translated'] ?? 0,
                ]);
            } catch (\Exception $e) {
                $processedLangs++;
                $results['languages'][$targetLang] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                Log::error('Translation failed for language', [
                    'card_id' => $this->cardId,
                    'target_lang' => $targetLang,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk translation completed', $results);

        // Clear progress cache
        Cache::forget("translation_progress:{$this->cardId}:{$this->userId}");

        // Cache translation completion for SSE
        Cache::put("translation_complete:{$this->cardId}:{$this->userId}", $results, now()->addMinutes(10));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk translation job failed', [
            'card_id' => $this->cardId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
