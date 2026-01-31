<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionLimitException extends Exception
{
    public function __construct(
        string $message = 'You have reached your subscription limit.',
        protected ?string $feature = null
    ) {
        parent::__construct($message);
    }

    public function getFeature(): ?string
    {
        return $this->feature;
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $this->getMessage(),
                'feature' => $this->feature,
                'upgrade_url' => route('subscription.index'),
            ], 403);
        }

        return redirect()->route('subscription.index')
            ->with('warning', $this->getMessage());
    }
}
