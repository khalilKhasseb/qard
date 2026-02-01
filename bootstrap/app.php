<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum middleware for SPA authentication - MUST be first
        $middleware->statefulApi();

        // Language middleware MUST run before HandleInertiaRequests
        // so that translations are loaded for the correct locale
        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
            \App\Http\Middleware\SetLanguageDirection::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Add security headers to API routes as well
        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Rate limiting for API routes - 60 requests per minute per user
        $middleware->throttleApi('60,1');

        $middleware->alias([
            'user.verified' => \App\Http\Middleware\EnsureUserIsVerified::class,
            'phone.verified' => \App\Http\Middleware\EnsurePhoneIsVerified::class,
            'subscribed' => \App\Http\Middleware\EnsureUserHasActiveSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 CSRF token mismatch gracefully for Inertia
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($response->getStatusCode() === 419) {
                // For Inertia requests, redirect back with a flash message
                if ($request->header('X-Inertia')) {
                    return redirect()->back()->with('error', __('Your session has expired. Please try again.'));
                }
            }

            return $response;
        });
    })->create();
