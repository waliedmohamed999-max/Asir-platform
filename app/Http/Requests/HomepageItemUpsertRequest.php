<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomepageItemUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $itemId = $this->route('homepage_item')?->id ?? $this->route('homepageItem')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('homepage_items', 'slug')->ignore($itemId)],
            'subtitle' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'hero_image_url' => ['nullable', 'url'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'string'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_gallery' => ['nullable', 'array'],
            'existing_gallery.*' => ['string'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'section_key' => ['required', 'string', 'max:100'],
            'ad_type' => ['nullable', 'string', 'max:100'],
            'content_type' => ['required', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'date_label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'schedule' => ['nullable', 'string'],
            'directions' => ['nullable', 'string'],
            'location_title' => ['nullable', 'string', 'max:255'],
            'location_code' => ['nullable', 'string', 'max:255'],
            'map_url' => ['nullable', 'url'],
            'price_label' => ['nullable', 'string', 'max:100'],
            'meta_label' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function payload(): array
    {
        return [
            'title' => $this->string('title')->toString(),
            'slug' => $this->filled('slug') ? Str::slug($this->string('slug')->toString()) : null,
            'subtitle' => $this->string('subtitle')->toString() ?: null,
            'image_url' => $this->string('image_url')->toString() ?: null,
            'hero_image_url' => $this->string('hero_image_url')->toString() ?: null,
            'gallery' => collect(preg_split('/\r\n|\r|\n/', (string) $this->input('gallery')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all(),
            'cta_label' => $this->string('cta_label')->toString() ?: null,
            'cta_url' => $this->string('cta_url')->toString() ?: null,
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
            'section_key' => $this->string('section_key')->toString(),
            'ad_type' => $this->string('ad_type')->toString() ?: null,
            'content_type' => $this->string('content_type')->toString(),
            'category_id' => $this->filled('category_id') ? $this->integer('category_id') : null,
            'city_id' => $this->filled('city_id') ? $this->integer('city_id') : null,
            'event_id' => $this->filled('event_id') ? $this->integer('event_id') : null,
            'venue_name' => $this->string('venue_name')->toString() ?: null,
            'date_label' => $this->string('date_label')->toString() ?: null,
            'description' => $this->string('description')->toString() ?: null,
            'includes' => $this->string('includes')->toString() ?: null,
            'terms' => $this->string('terms')->toString() ?: null,
            'schedule' => $this->string('schedule')->toString() ?: null,
            'directions' => $this->string('directions')->toString() ?: null,
            'location_title' => $this->string('location_title')->toString() ?: null,
            'location_code' => $this->string('location_code')->toString() ?: null,
            'map_url' => $this->string('map_url')->toString() ?: null,
            'price_label' => $this->string('price_label')->toString() ?: null,
            'meta_label' => $this->string('meta_label')->toString() ?: null,
            'badge' => $this->string('badge')->toString() ?: null,
            'rating' => $this->filled('rating') ? (float) $this->input('rating') : null,
            'sort_order' => (int) $this->input('sort_order', 0),
            'starts_at' => $this->input('starts_at') ?: null,
            'ends_at' => $this->input('ends_at') ?: null,
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}
