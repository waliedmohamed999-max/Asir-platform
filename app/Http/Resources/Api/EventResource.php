<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiImageUrl;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', app()->getLocale());
        $title = $lang === 'en' ? ($this->title_en ?: $this->title) : ($this->title_ar ?: $this->title);
        $description = $lang === 'en' ? ($this->description_en ?: $this->description) : ($this->description_ar ?: $this->description);

        return [
            'id' => $this->id,
            'title' => $title,
            'title_ar' => $this->title_ar ?: $this->title,
            'title_en' => $this->title_en,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'description' => $description,
            'description_ar' => $this->description_ar ?: $this->description,
            'description_en' => $this->description_en,
            'image_url' => ApiImageUrl::make($this->primary_image_url),
            'banner_image_url' => ApiImageUrl::make($this->banner_image_url),
            'gallery' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => ApiImageUrl::make($image->image_url),
                'alt' => $image->alt_text,
            ])->values()),
            'starts_at' => $this->start_date?->toIso8601String(),
            'ends_at' => $this->end_date?->toIso8601String(),
            'city' => new CityResource($this->whenLoaded('city')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'venue_name' => $this->venue_name,
            'venue_name_en' => $this->venue_name_en,
            'map_url' => $this->map_url,
            'location' => [
                'lat' => $this->location_lat ? (float) $this->location_lat : null,
                'lng' => $this->location_lng ? (float) $this->location_lng : null,
            ],
            'terms' => $this->terms,
            'refund_policy' => $this->refund_policy,
            'schedule_notes' => $this->schedule_notes,
            'faqs' => $this->faqs ?? [],
            'is_featured' => $this->is_featured,
            'organizer' => $this->whenLoaded('organizer', fn () => [
                'id' => $this->organizer?->id,
                'name' => $this->organizer?->name,
                'logo_url' => ApiImageUrl::make($this->organizer?->logo_url),
            ]),
            'social' => [
                'rating_average' => round((float) ($this->reviews_avg_rating ?? 0), 1),
                'reviews_count' => (int) ($this->reviews_count ?? 0),
                'bookings_count' => (int) ($this->bookings_count ?? 0),
            ],
            'is_free' => (float) ($this->tickets_min_price ?? $this->tickets->min('price') ?? 0) <= 0,
            'starting_price' => (float) ($this->tickets_min_price ?? $this->tickets->min('price') ?? 0),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
        ];
    }
}
