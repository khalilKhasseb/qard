<?php

namespace App\Services\Sms\Providers;

use App\Contracts\Sms\SmsProvider;
use App\Contracts\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Twilio SMS Provider.
 *
 * Sends SMS messages via Twilio's REST API.
 *
 * Required config:
 * - sms.providers.twilio.account_sid
 * - sms.providers.twilio.auth_token
 * - sms.providers.twilio.from_number
 * - Recovery code : 5QPFK1278D9AUD989MRXZSBH
 */
class TwilioSmsProvider implements SmsProvider
{
    protected string $accountSid;

    protected string $authToken;

    protected string $fromNumber;

    protected string $baseUrl = 'https://api.twilio.com/2010-04-01';

    public function __construct()
    {
        $this->accountSid = config('sms.providers.twilio.account_sid', '');
        $this->authToken = config('sms.providers.twilio.auth_token', '');
        $this->fromNumber = config('sms.providers.twilio.from_number', '');
    }

    public function send(string $to, string $message): SmsResult
    {
        if (! $this->isConfigured()) {
            return SmsResult::failure('Twilio is not properly configured');
        }

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post("{$this->baseUrl}/Accounts/{$this->accountSid}/Messages.json", [
                    'To' => $to,
                    'From' => $this->fromNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return SmsResult::success($data['sid'] ?? null, [
                    'provider' => $this->getName(),
                    'status' => $data['status'] ?? 'unknown',
                ]);
            }

            $error = $response->json('message') ?? $response->body();
            Log::error('Twilio SMS failed', [
                'to' => $to,
                'error' => $error,
                'status' => $response->status(),
            ]);

            return SmsResult::failure($error);

        } catch (\Exception $e) {
            Log::error('Twilio SMS exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return SmsResult::failure($e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'twilio';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->accountSid)
            && ! empty($this->authToken)
            && ! empty($this->fromNumber);
    }
}
