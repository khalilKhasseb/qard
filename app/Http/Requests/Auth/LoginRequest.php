<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->input('identifier');
        $identifierType = $this->detectIdentifierType($identifier);
        $authSettings = app(AuthSettings::class);

        // Check if the login method is allowed
        if ($identifierType === 'email' && ! $authSettings->allow_email_login) {
            throw ValidationException::withMessages([
                'identifier' => trans('auth.email_login_disabled'),
            ]);
        }

        if ($identifierType === 'phone' && ! $authSettings->allow_phone_login) {
            throw ValidationException::withMessages([
                'identifier' => trans('auth.phone_login_disabled'),
            ]);
        }

        $user = $this->findUser($identifier, $identifierType);

        if (! $user || ! Auth::attempt([
            'email' => $user->email,
            'password' => $this->input('password'),
        ], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'identifier' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Detect if the identifier is an email or phone number.
     */
    protected function detectIdentifierType(string $identifier): string
    {
        // If it contains @, treat as email
        if (str_contains($identifier, '@')) {
            return 'email';
        }

        return 'phone';
    }

    /**
     * Find a user by email or phone number.
     */
    protected function findUser(string $identifier, string $type): ?User
    {
        if ($type === 'email') {
            return User::where('email', strtolower($identifier))->first();
        }

        // Normalize phone number for lookup
        $normalizedPhone = $this->normalizePhone($identifier);

        return User::where('phone', $normalizedPhone)->first();
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

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identifier' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('identifier')).'|'.$this->ip());
    }
}
