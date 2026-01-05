<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'is_system_default' => $this->is_system_default,
            'is_public' => $this->is_public,
            'config' => $this->config,
            'preview_image' => $this->preview_image,
            'used_by_cards_count' => $this->used_by_cards_count,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // User info
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? null,
            ],
        ];
    }
}
