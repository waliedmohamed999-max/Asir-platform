<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiImageUrl;

class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'city' => new CityResource($this->whenLoaded('city')),
            'address' => $this->address,
            'map_url' => $this->google_maps_url,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'image_url' => ApiImageUrl::make($this->image_url),
            'description' => $this->description,
            'capacity' => $this->capacity,
        ];
    }
}
