<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class UpdateSubscriptionPlanTranslationCreditsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update Free plan
        $free = SubscriptionPlan::where('name', 'Free')
            ->orWhere('slug', 'free')
            ->first();
            
        if ($free) {
            $features = $free->features ?? [];
            $features['ai_translation'] = true;
            
            $free->update([
                'translation_credits_monthly' => 10,
                'unlimited_translations' => false,
                'per_credit_cost' => 0.01,
                'features' => $features,
            ]);
        }

        // Update Pro plan (assuming it's called Pro)
        $pro = SubscriptionPlan::where('name', 'Pro')
            ->orWhere('slug', 'pro')
            ->first();
            
        if ($pro) {
            $features = $pro->features ?? [];
            $features['ai_translation'] = true;
            
            $pro->update([
                'translation_credits_monthly' => 100,
                'unlimited_translations' => false,
                'per_credit_cost' => 0.005,
                'features' => $features,
            ]);
        }

        // Update Business/Enterprise plan (assuming Business)
        $business = SubscriptionPlan::where('name', 'Business')
            ->orWhere('slug', 'business')
            ->first();
            
        if ($business) {
            $features = $business->features ?? [];
            $features['ai_translation'] = true;
            
            $business->update([
                'translation_credits_monthly' => 0,
                'unlimited_translations' => true,
                'per_credit_cost' => 0,
                'features' => $features,
            ]);
        }

        $this->command->info('Subscription plans updated with translation credits and features.');
    }
}
