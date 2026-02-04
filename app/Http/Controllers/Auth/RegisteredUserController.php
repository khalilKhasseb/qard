<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\WelcomeEmail;
use App\Services\Sms\OtpManager;
use App\Settings\AuthSettings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(protected OtpManager $otpManager) {}

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        return Inertia::render('Auth/Register', [
            'plans' => $plans,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalize phone BEFORE validation so unique check works on normalized value
        if ($request->has('phone')) {
            $request->merge([
                'phone' => $this->normalizePhone($request->phone),
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|min:10|max:20|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone, // Already normalized
            'password' => Hash::make($request->password),
            'pending_plan_id' => $request->plan_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        // Redirect based on verification method setting
        $authSettings = app(AuthSettings::class);

        if ($authSettings->verification_method === 'phone') {
            // Send OTP for phone verification
            $this->otpManager->send($request->phone, 'registration');

            // Send welcome email (non-blocking)
            $user->notify(new WelcomeEmail);

            return redirect(route('phone.verification.notice', absolute: false));
        }

        // Send email verification notification directly
        $user->sendEmailVerificationNotification();

        return redirect(route('verification.notice', absolute: false));
    }

    /**
     * Normalize phone number to E.164 format.
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
