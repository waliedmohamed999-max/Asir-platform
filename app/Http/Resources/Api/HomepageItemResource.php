<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiImageUrl;

class HomepageItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'section_key' => $this->section_key,
            'content_type' => $this->content_type,
            'ad_type' => $this->ad_type,
            'image_url' => ApiImageUrl::make($this->image_url),
            'hero_image_url' => ApiImageUrl::make($this->hero_image_url),
            'gallery' => collect($this->gallery ?? [])->map(fn ($url) => ApiImageUrl::make($url))->filter()->values(),
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'price_label' => $this->price_label,
            'meta_label' => $this->meta_label,
            'badge' => $this->badge,
            'rating' => $this->rating ? (float) $this->rating : null,
            'venue_name' => $this->venue_name,
            'date_label' => $this->date_label,
            'description' => $this->description,
            'ends_at' => $this->ends_at?->toIso8601String(),
            'event' => new EventResource($this->whenLoaded('event')),
        ];
    }
}
