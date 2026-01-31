<?php

namespace App\Services\Sms\Providers;

use App\Contracts\Sms\SmsProvider;
use App\Contracts\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vonage (formerly Nexmo) SMS Provider.
 *
 * Sends SMS messages via Vonage's REST API.
 *
 * Required config:
 * - sms.providers.vonage.api_key
 * - sms.providers.vonage.api_secret
 * - sms.providers.vonage.from_name
 */
class VonageSmsProvider implements SmsProvider
{
    protected string $apiKey;

    protected string $apiSecret;

    protected string $fromName;

    protected string $baseUrl = 'https://rest.nexmo.com';

    public function __construct()
    {
        $this->apiKey = config('sms.providers.vonage.api_key', '');
        $this->apiSecret = config('sms.providers.vonage.api_secret', '');
        $this->fromName = config('sms.providers.vonage.from_name', config('app.name'));
    }

    public function send(string $to, string $message): SmsResult
    {
        if (! $this->isConfigured()) {
            return SmsResult::failure('Vonage is not properly configured');
        }

        try {
            $response = Http::asForm()
                ->post("{$this->baseUrl}/sms/json", [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'to' => $to,
                    'from' => $this->fromName,
                    'text' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $messages = $data['messages'] ?? [];

                if (! empty($messages) && ($messages[0]['status'] ?? '1') === '0') {
                    return SmsResult::success($messages[0]['message-id'] ?? null, [
                        'provider' => $this->getName(),
                        'remaining_balance' => $messages[0]['remaining-balance'] ?? null,
                    ]);
                }

                $error = $messages[0]['error-text'] ?? 'Unknown error';
                Log::error('Vonage SMS failed', [
                    'to' => $to,
                    'error' => $error,
                ]);

                return SmsResult::failure($error);
            }

            return SmsResult::failure($response->body());

        } catch (\Exception $e) {
            Log::error('Vonage SMS exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return SmsResult::failure($e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'vonage';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->apiSecret);
    }
}
