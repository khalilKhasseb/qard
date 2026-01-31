<?php

namespace App\Http\Middleware;

use App\Settings\AuthSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $authSettings = app(AuthSettings::class);
        $verificationMethod = $authSettings->verification_method;

        $isVerified = match ($verificationMethod) {
            'email' => $request->user()->hasVerifiedEmail(),
            'phone' => $request->user()->hasVerifiedPhone(),
            default => $request->user()->hasVerifiedEmail(),
        };

        if (! $isVerified) {
            $redirectRoute = match ($verificationMethod) {
                'email' => 'verification.notice',
                'phone' => 'phone.verification.notice',
                default => 'verification.notice',
            };

            return $request->expectsJson()
                ? abort(403, 'Your account is not verified.')
                : Redirect::guest(URL::route($redirectToRoute ?: $redirectRoute));
        }

        return $next($request);
    }
}
