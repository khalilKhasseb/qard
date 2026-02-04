<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Sms\OtpManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhoneVerificationController extends Controller
{
    public function __construct(protected OtpManager $otpManager) {}

    /**
     * Display the phone verification prompt.
     */
    public function notice(Request $request): Response|RedirectResponse
    {
        \Log::info('Start Notice');
        $user = $request->user();

        \Log::info('User', ['user' => $user]);

        if ($user->hasVerifiedPhone()) {
            \Log::info('Phone already verified, redirecting to intended page');

            return redirect()->intended($user->getPostVerificationRedirect());
        }

        if (! $user->phone) {
            \Log::info('User Has no PPhone, redirecting to phone update page');

            return redirect()->route('phone.update');
        }

        // Get current OTP status
        \Log::info('Checking OTP status for user', ['phone' => $user->phone]);
        $hasOtp = $this->otpManager->hasValidOtp($user->phone, 'registration');
        \Log::info('OTP status', ['hasOtp' => $hasOtp]);
        $cooldown = $this->otpManager->getCooldownSeconds($user->phone, 'registration');
        \Log::info('CoolDown stats', [
            'cooldown' => $cooldown,
        ]);
        $remainingAttempts = $this->otpManager->getRemainingAttempts($user->phone, 'registration');
        \Log::info('Remaining Attempts', [
            'remainingAttempts' => $remainingAttempts,
        ]);

        if (! $hasOtp && $cooldown === 0) {
            // Send OTP if not already sent and no cooldown
            \Log::info('No valid OTP and no cooldown, sending new OTP');
            $result = $this->otpManager->send($user->phone, 'registration');
            \Log::info('OTP send result', [
                'success' => $result->isSuccess(),
                'rateLimited' => $result->isRateLimited(),
                'error' => $result->error,
            ]);
        }

        return Inertia::render('Auth/VerifyPhone', [
            'phone' => $this->maskPhone($user->phone),
            'hasOtp' => $hasOtp,
            'cooldownSeconds' => $cooldown,
            'remainingAttempts' => $remainingAttempts,
            'status' => session('status'),
        ]);
    }

    /**
     * Send OTP to user's phone.
     */
    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number on file.',
            ], 422);
        }

        if ($user->hasVerifiedPhone()) {
            return response()->json([
                'success' => false,
                'message' => 'Phone already verified.',
            ], 422);
        }

        $result = $this->otpManager->send($user->phone, 'registration');

        if ($result->isRateLimited()) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another code.',
                'cooldownSeconds' => $result->cooldownSeconds,
            ], 429);
        }

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => 'Verification code sent.',
                'expiresInSeconds' => $result->expiresInSeconds,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->error ?? 'Failed to send verification code.',
        ], 500);
    }

    /**
     * Verify the OTP code.
     */
    public function verify(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $user->phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number on file.',
            ], 422);
        }

        if ($user->hasVerifiedPhone()) {
            return response()->json([
                'success' => false,
                'message' => 'Phone already verified.',
            ], 422);
        }

        $isValid = $this->otpManager->verify($user->phone, $request->code, 'registration');

        if ($isValid) {
            $user->markPhoneAsVerified();

            $redirectUrl = $user->getPostVerificationRedirect();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phone verified successfully.',
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect()->intended($redirectUrl)
                ->with('status', 'phone-verified');
        }

        $remainingAttempts = $this->otpManager->getRemainingAttempts($user->phone, 'registration');

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code.',
            'remainingAttempts' => $remainingAttempts,
        ], 422);
    }

    /**
     * Resend OTP code.
     */
    public function resend(Request $request): JsonResponse
    {
        return $this->send($request);
    }

    /**
     * Show form to update phone number.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Auth/UpdatePhone', [
            'currentPhone' => $request->user()->phone ? $this->maskPhone($request->user()->phone) : null,
        ]);
    }

    /**
     * Update user's phone number.
     */
    public function update(Request $request): RedirectResponse
    {
        // Normalize phone BEFORE validation so unique check works on normalized value
        if ($request->has('phone')) {
            $request->merge([
                'phone' => $this->normalizePhone($request->phone),
            ]);
        }

        $request->validate([
            'phone' => ['required', 'string', 'min:10', 'max:20', 'unique:users,phone,'.$request->user()->id],
        ]);

        $request->user()->update([
            'phone' => $request->phone, // Already normalized
            'phone_verified_at' => null, // Reset verification when phone changes
        ]);

        // Send OTP to new number
        $this->otpManager->send($request->phone, 'registration');

        return redirect()->route('phone.verification.notice')
            ->with('status', 'Verification code sent to your new number.');
    }

    /**
     * Mask phone number for display.
     */
    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, 3).str_repeat('*', $length - 5).substr($phone, -2);
    }

    /**
     * Normalize phone number.
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters except +
        $normalized = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure it starts with +
        if (! str_starts_with($normalized, '+')) {
            $normalized = '+'.$normalized;
        }

        return $normalized;
    }
}
