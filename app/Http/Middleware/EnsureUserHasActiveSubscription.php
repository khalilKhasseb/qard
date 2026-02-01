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

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins bypass subscription check
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check for active subscription via UserSubscription model
        $hasActiveSubscription = $user->activeSubscription()
            ->where('status', 'active')
            ->exists();

        if ($hasActiveSubscription) {
            return $next($request);
        }

        // No active subscription - redirect to plan selection
        return redirect()->route('subscription.index')
            ->with('warning', __('Please select a subscription plan to continue.'));
    }
}
