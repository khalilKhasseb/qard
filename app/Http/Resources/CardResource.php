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
            'user_id' => $this->user_id,
            'language_id' => $this->language_id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'cover_image_path' => $this->cover_image_path,
            'cover_image_url' => $this->cover_image_url,
            'profile_image_path' => $this->profile_image_path,
            'profile_image_url' => $this->profile_image_url,
            'template_id' => $this->template_id,
            'theme_id' => $this->theme_id,
            'theme_overrides' => $this->theme_overrides,
            'active_languages' => $this->active_languages ?? [],
            'draft_data' => $this->draft_data,
            'custom_slug' => $this->custom_slug,
            'share_url' => $this->share_url,
            'qr_code_url' => $this->qr_code_url,
            'nfc_identifier' => $this->nfc_identifier,
            'is_published' => $this->is_published,
            'is_primary' => $this->is_primary,
            'views_count' => $this->views_count,
            'shares_count' => $this->shares_count,
            'full_url' => $this->full_url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships (when loaded)
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            'user' => $this->when($this->relationLoaded('user'), fn () => [
                'id' => $this->user_id,
                'name' => $this->user->name,
            ], [
                'id' => $this->user_id,
            ]),
        ];
    }
}
