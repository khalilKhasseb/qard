<?php

namespace App\Facades;

use App\Contracts\Sms\OtpResult;
use App\Services\Sms\OtpManager;
use Illuminate\Support\Facades\Facade;

/**
 * OTP Facade
 *
 * @method static OtpResult send(string $phone, string $purpose = 'verification')
 * @method static bool verify(string $phone, string $code, string $purpose = 'verification')
 * @method static OtpResult resend(string $phone, string $purpose = 'verification')
 * @method static bool hasValidOtp(string $phone, string $purpose = 'verification')
 * @method static int getCooldownSeconds(string $phone, string $purpose = 'verification')
 * @method static void invalidate(string $phone, string $purpose = 'verification')
 * @method static int getRemainingAttempts(string $phone, string $purpose = 'verification')
 *
 * @see \App\Services\Sms\OtpManager
 */
class Otp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OtpManager::class;
    }
}
