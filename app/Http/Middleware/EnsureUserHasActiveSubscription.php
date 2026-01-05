<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Allow access if user has active subscription or is on free tier
        if ($user->hasActiveSubscription() || $user->subscription_tier === 'free') {
            return $next($request);
        }

        // Redirect to payment page if subscription is expired/canceled
        return redirect()->route('payments.index')
            ->with('warning', 'Please renew your subscription to continue.');
    }
}
