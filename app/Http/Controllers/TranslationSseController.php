<?php

namespace App\Http\Controllers;

use App\Models\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationSseController extends Controller
{
    /**
     * Stream translation events for a specific card.
     */
    public function streamEvents(Request $request, int $cardId): StreamedResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }

        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');
        @ini_set('implicit_flush', '1');

        $user = $request->user();
        
        // Verify the user owns this card
        $card = BusinessCard::where('id', $cardId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Set SSE headers
        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ];

        return response()->stream(function () use ($user, $cardId) {
            // Send initial connection confirmation
            echo "data: " . json_encode([
                'type' => 'connected',
                'timestamp' => now()->toISOString(),
                'credits' => $user->getRemainingTranslationCredits(),
            ]) . "\n\n";

            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();

            $startTime = time();
            $maxDuration = 300; // 5 minutes max connection time
            $lastHeartbeat = $startTime;
            $heartbeatInterval = 30;

            while (true) {
                // Check for timeout
                if (time() - $startTime > $maxDuration) {
                    echo "data: " . json_encode([
                        'type' => 'timeout',
                        'message' => 'Connection timeout',
                    ]) . "\n\n";
                    break;
                }

                // Check for translation completion
                $translationKey = "translation_complete:{$cardId}:{$user->id}";
                $translationResult = Cache::get($translationKey);
                
                if ($translationResult) {
                    echo "data: " . json_encode([
                        'type' => 'translation_complete',
                        'cardId' => $cardId,
                        'result' => $translationResult,
                        'credits' => $user->refresh()->getRemainingTranslationCredits(),
                        'timestamp' => now()->toISOString(),
                    ]) . "\n\n";
                    
                    // Clear the completion flag
                    Cache::forget($translationKey);

                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                    
                    // Close connection after sending completion
                    sleep(1);
                    break;
                }

                // Check for credit updates
                $creditKey = "credits_updated:{$user->id}";
                if (Cache::get($creditKey)) {
                    echo "data: " . json_encode([
                        'type' => 'credits_updated',
                        'credits' => $user->refresh()->getRemainingTranslationCredits(),
                        'timestamp' => now()->toISOString(),
                    ]) . "\n\n";
                    
                    Cache::forget($creditKey);

                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                }

                // Send heartbeat every 30 seconds
                if (time() - $lastHeartbeat >= $heartbeatInterval) {
                    echo "data: " . json_encode([
                        'type' => 'heartbeat',
                        'timestamp' => now()->toISOString(),
                    ]) . "\n\n";

                    $lastHeartbeat = time();

                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                }

                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }

                sleep(1);
            }
        }, 200, $headers);
    }
}