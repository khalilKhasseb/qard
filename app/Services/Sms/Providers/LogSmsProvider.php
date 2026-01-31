<?php

namespace App\Services\Sms\Providers;

use App\Contracts\Sms\SmsProvider;
use App\Contracts\Sms\SmsResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Log SMS Provider - For development and testing.
 *
 * Logs SMS messages instead of sending them. Useful for development
 * and testing without incurring SMS costs.
 */
class LogSmsProvider implements SmsProvider
{
    public function __construct(
        protected string $channel = 'stack'
    ) {}

    public function send(string $to, string $message): SmsResult
    {
        $messageId = 'log_'.Str::random(16);

        Log::channel($this->channel)->info('SMS Message', [
            'provider' => $this->getName(),
            'message_id' => $messageId,
            'to' => $to,
            'message' => $message,
            'length' => strlen($message),
        ]);

        return SmsResult::success($messageId, [
            'provider' => $this->getName(),
            'logged_at' => now()->toIso8601String(),
        ]);
    }

    public function getName(): string
    {
        return 'log';
    }

    public function isConfigured(): bool
    {
        return true; // Always configured since it just logs
    }
}
