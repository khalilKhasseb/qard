<?php

namespace App\Jobs;

use App\Models\TranslationHistory;
use App\Services\AiTranslationProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class VerifyTranslationQuality implements ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $translationHistoryId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiTranslationProvider $aiProvider): void
    {
        $translation = TranslationHistory::find($this->translationHistoryId);

        if (! $translation) {
            Log::error('Translation history not found for quality verification', [
                'translation_id' => $this->translationHistoryId,
            ]);

            return;
        }

        try {
            // Verify quality using AI
            $quality = $aiProvider->verifyQuality(
                $translation->source_text,
                $translation->translated_text,
                $translation->source_language,
                $translation->target_language
            );

            // Update translation with quality score
            $translation->calculateQualityScore($quality['score']);

            // Store feedback in metadata
            $metadata = $translation->metadata ?? [];
            $metadata['quality_feedback'] = $quality['feedback'];
            $metadata['verified_at'] = now()->toDateTimeString();
            $translation->metadata = $metadata;
            $translation->save();

            Log::info('Translation quality verified', [
                'translation_id' => $this->translationHistoryId,
                'score' => $quality['score'],
                'status' => $translation->verification_status,
            ]);
        } catch (\Exception $e) {
            Log::error('Quality verification failed', [
                'translation_id' => $this->translationHistoryId,
                'error' => $e->getMessage(),
            ]);

            // Mark as needs review on error
            $translation->update(['verification_status' => 'needs_review']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Quality verification job failed', [
            'translation_id' => $this->translationHistoryId,
            'error' => $exception->getMessage(),
        ]);
    }
}
