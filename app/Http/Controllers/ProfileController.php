<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Sms\OtpManager;
use App\Settings\AuthSettings;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(protected OtpManager $otpManager) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $authSettings = app(AuthSettings::class);

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'verificationMethod' => $authSettings->verification_method,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Check if email changed
        $emailChanged = $user->email !== $validated['email'];

        // Check if phone changed
        $phoneChanged = $user->phone !== $validated['phone'];

        $user->fill($validated);

        // Reset email verification if email changed
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        // Reset phone verification if phone changed
        if ($phoneChanged) {
            $user->phone_verified_at = null;
        }

        $user->save();

        // If phone changed, send OTP for re-verification
        if ($phoneChanged) {
            $this->otpManager->send($validated['phone'], 'registration');

            return Redirect::route('phone.verification.notice')
                ->with('status', 'Phone number updated. Please verify your new number.');
        }

        // Determine what changed for the status message
        $statusMessage = null;
        if ($emailChanged) {
            $statusMessage = 'profile-updated-verify-email';
        }

        return Redirect::route('profile.edit')->with('status', $statusMessage);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Use the web guard for logout to ensure session-based authentication is cleared
        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
