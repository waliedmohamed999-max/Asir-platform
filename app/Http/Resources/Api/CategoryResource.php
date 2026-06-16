<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ApiImageUrl;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', app()->getLocale());

        return [
            'id' => $this->id,
            'name' => $lang === 'en' ? ($this->name_en ?: $this->name) : ($this->name_ar ?: $this->name),
            'name_ar' => $this->name_ar ?: $this->name,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'image_url' => ApiImageUrl::make($this->image_url),
        ];
    }
}
