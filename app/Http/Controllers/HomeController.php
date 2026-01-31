<?php

namespace App\Http\Controllers;

use App\Models\BusinessCard;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {

        // Get active pricing plans from database
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'cards_limit' => $plan->cards_limit,
                'themes_limit' => $plan->themes_limit,
                'features' => $this->buildPlanFeatures($plan),
                'is_popular' => $plan->slug === 'pro',
            ]);

        // Get a sample public card for the preview (if exists)
        $sampleCard = BusinessCard::query()
            ->where('is_published', true)
            ->with(['sections' => fn ($q) => $q->active()->ordered(), 'theme'])
            ->inRandomOrder()
            ->first();

        // Stats for social proof
        $stats = [
            'users' => User::count(),
            'cards' => BusinessCard::count(),
            'views' => BusinessCard::sum('views_count'),
        ];

        return Inertia::render('WelcomeTw', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'plans' => $plans,
            'sampleCard' => $sampleCard,
            'stats' => $stats,
        ]);
    }

    private function buildPlanFeatures(SubscriptionPlan $plan): array
    {
        $features = [];

        // Card limit
        if ($plan->cards_limit >= 999) {
            $features[] = 'Unlimited Cards';
        } else {
            $features[] = $plan->cards_limit.' '.($plan->cards_limit === 1 ? 'Card' : 'Cards');
        }

        // Theme limit
        if ($plan->themes_limit >= 999) {
            $features[] = 'Unlimited Themes';
        } else {
            $features[] = $plan->themes_limit.' Custom '.($plan->themes_limit === 1 ? 'Theme' : 'Themes');
        }

        // Analytics
        if ($plan->analytics_enabled) {
            $features[] = 'Advanced Analytics';
        } else {
            $features[] = 'Basic Analytics';
        }

        // QR Code (always available)
        $features[] = 'QR Code Sharing';

        // NFC
        if ($plan->nfc_enabled) {
            $features[] = 'NFC Card Support';
        }

        // Custom CSS
        if ($plan->custom_css_allowed) {
            $features[] = 'Custom CSS Styling';
        }

        // Custom Domain
        if ($plan->custom_domain_allowed) {
            $features[] = 'Custom Domain';
        }

        // Translations
        if ($plan->unlimited_translations) {
            $features[] = 'Unlimited Translations';
        } elseif ($plan->translation_credits_monthly > 0) {
            $features[] = $plan->translation_credits_monthly.' Translation Credits/mo';
        }

        return $features;
    }
}
