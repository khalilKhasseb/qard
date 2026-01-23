<?php

namespace App\Filament\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscriptionPlan extends CreateRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Clean up empty keys from the features array
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = array_filter(
                $data['features'],
                fn ($key) => $key !== '',
                ARRAY_FILTER_USE_KEY
            );
            
            // If all entries were removed, set to null
            $data['features'] = empty($data['features']) ? null : $data['features'];
        }

        return $data;
    }
}
