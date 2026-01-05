<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'template_id' => $this->template_id,
            'theme_id' => $this->theme_id,
            'theme_overrides' => $this->theme_overrides,
            'custom_slug' => $this->custom_slug,
            'share_url' => $this->share_url,
            'qr_code_url' => $this->qr_code_url,
            'nfc_identifier' => $this->nfc_identifier,
            'is_published' => $this->is_published,
            'views_count' => $this->views_count,
            'shares_count' => $this->shares_count,
            'full_url' => $this->full_url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships (when loaded)
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? null,
            ],
        ];
    }
}
