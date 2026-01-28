<?php

namespace App\Filament\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Resources\SubscriptionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionPlan extends EditRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Clean up empty keys from the features array BEFORE form load
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = array_filter(
                $data['features'],
                fn ($key) => $key !== '',
                ARRAY_FILTER_USE_KEY
            );

            // If all entries were removed, set to empty array instead of null
            $data['features'] = empty($data['features']) ? [] : $data['features'];
        } else {
            $data['features'] = [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
