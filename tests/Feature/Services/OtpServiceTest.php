<?php

use App\Services\Sms\OtpManager;
use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear any existing OTPs
    Cache::flush();

    $this->smsManager = app(SmsManager::class);
    $this->otpManager = new OtpManager($this->smsManager);
    $this->phone = '+1234567890';
});

test('can send OTP', function () {
    $result = $this->otpManager->send($this->phone);

    expect($result->isSuccess())->toBeTrue();
    expect($result->expiresInSeconds)->toBeGreaterThan(0);
});

test('enforces cooldown between OTP requests', function () {
    // First send should succeed
    $result1 = $this->otpManager->send($this->phone);
    expect($result1->isSuccess())->toBeTrue();

    // Second immediate send should be rate limited
    $result2 = $this->otpManager->send($this->phone);
    expect($result2->isRateLimited())->toBeTrue();
    expect($result2->cooldownSeconds)->toBeGreaterThan(0);
});

test('can verify correct OTP', function () {
    // We need to access the cache directly to get the code for testing
    $this->otpManager->send($this->phone);

    $cacheKey = "otp:verification:{$this->phone}";
    $storedData = Cache::get($cacheKey);

    expect($storedData)->not->toBeNull();
    expect($storedData['code'])->toHaveLength(config('sms.otp.length', 6));

    $isValid = $this->otpManager->verify($this->phone, $storedData['code']);
    expect($isValid)->toBeTrue();
});

test('rejects incorrect OTP', function () {
    $this->otpManager->send($this->phone);

    $isValid = $this->otpManager->verify($this->phone, '000000');
    expect($isValid)->toBeFalse();
});

test('tracks verification attempts', function () {
    $this->otpManager->send($this->phone);

    // Wrong attempt
    $this->otpManager->verify($this->phone, '000000');

    $remaining = $this->otpManager->getRemainingAttempts($this->phone);
    expect($remaining)->toBe(config('sms.otp.max_attempts', 5) - 1);
});

test('OTP is invalidated after successful verification', function () {
    $this->otpManager->send($this->phone);

    $cacheKey = "otp:verification:{$this->phone}";
    $storedData = Cache::get($cacheKey);

    $this->otpManager->verify($this->phone, $storedData['code']);

    expect($this->otpManager->hasValidOtp($this->phone))->toBeFalse();
});

test('can check if valid OTP exists', function () {
    expect($this->otpManager->hasValidOtp($this->phone))->toBeFalse();

    $this->otpManager->send($this->phone);

    expect($this->otpManager->hasValidOtp($this->phone))->toBeTrue();
});

test('can manually invalidate OTP', function () {
    $this->otpManager->send($this->phone);
    expect($this->otpManager->hasValidOtp($this->phone))->toBeTrue();

    $this->otpManager->invalidate($this->phone);
    expect($this->otpManager->hasValidOtp($this->phone))->toBeFalse();
});

test('normalizes phone numbers', function () {
    // These should all be treated as the same phone
    $phone1 = '+1234567890';
    $phone2 = '1234567890';
    $phone3 = '+1 234 567 890';

    $this->otpManager->send($phone1);

    // After normalization, all should refer to the same OTP
    expect($this->otpManager->hasValidOtp($phone2))->toBeTrue();
});

test('different purposes have separate OTPs', function () {
    // Use different phone numbers to avoid cooldown issues
    $phone1 = '+1234567891';
    $phone2 = '+1234567892';

    $this->otpManager->send($phone1, 'registration');
    $this->otpManager->send($phone2, 'login');

    // Each purpose/phone combo has its own OTP
    expect($this->otpManager->hasValidOtp($phone1, 'registration'))->toBeTrue();
    expect($this->otpManager->hasValidOtp($phone2, 'login'))->toBeTrue();

    // But different purposes don't interfere
    expect($this->otpManager->hasValidOtp($phone1, 'login'))->toBeFalse();
    expect($this->otpManager->hasValidOtp($phone2, 'registration'))->toBeFalse();
});
