<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'city_id' => ['required', 'exists:cities,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_name_en' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'terms' => ['nullable', 'string'],
            'refund_policy' => ['nullable', 'string'],
            'schedule_notes' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url'],
            'banner_image_url' => ['nullable', 'url'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_banner_image' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'url'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'sold_out', 'ended', 'cancelled'])],
            'is_featured' => ['nullable', 'boolean'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'string'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_gallery_images' => ['nullable', 'array'],
            'existing_gallery_images.*' => ['string'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['nullable', 'string'],
            'tickets' => ['nullable', 'array'],
            'tickets.*.name' => ['required_with:tickets.*.price', 'nullable', 'string', 'max:255'],
            'tickets.*.type' => ['nullable', 'string', 'max:100'],
            'tickets.*.price' => ['nullable', 'numeric', 'min:0'],
            'tickets.*.price_before_discount' => ['nullable', 'numeric', 'min:0'],
            'tickets.*.quantity' => ['nullable', 'integer', 'min:0'],
            'tickets.*.description' => ['nullable', 'string'],
            'tickets.*.features' => ['nullable', 'string'],
            'tickets.*.purchase_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'tickets.*.label_color' => ['nullable', 'string', 'max:20'],
            'tickets.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'tickets.*.status' => ['nullable', Rule::in(['active', 'inactive', 'sold_out'])],
            'tickets.*.starts_at' => ['nullable', 'date'],
            'tickets.*.ends_at' => ['nullable', 'date'],
            'tickets.*.is_active' => ['nullable', 'boolean'],
            'tickets.*.is_hidden' => ['nullable', 'boolean'],
            'tickets.*.uses_qr' => ['nullable', 'boolean'],
        ];
    }

    public function eventData(): array
    {
        return [
            'title' => $this->string('title')->toString(),
            'title_en' => $this->string('title_en')->toString() ?: null,
            'city_id' => $this->integer('city_id'),
            'category_id' => $this->integer('category_id'),
            'venue_name' => $this->string('venue_name')->toString(),
            'venue_name_en' => $this->string('venue_name_en')->toString() ?: null,
            'excerpt' => $this->string('excerpt')->toString() ?: null,
            'description' => $this->string('description')->toString(),
            'terms' => $this->string('terms')->toString() ?: null,
            'refund_policy' => $this->string('refund_policy')->toString() ?: null,
            'schedule_notes' => $this->string('schedule_notes')->toString() ?: null,
            'map_url' => $this->string('map_url')->toString() ?: null,
            'banner_image_url' => $this->string('banner_image_url')->toString() ?: null,
            'video_url' => $this->string('video_url')->toString() ?: null,
            'location_lat' => $this->filled('location_lat') ? (float) $this->input('location_lat') : null,
            'location_lng' => $this->filled('location_lng') ? (float) $this->input('location_lng') : null,
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'status' => $this->string('status')->toString(),
            'is_featured' => $this->boolean('is_featured'),
            'show_on_homepage' => $this->boolean('show_on_homepage'),
            'display_order' => (int) $this->input('display_order', 0),
            'meta_title' => $this->string('meta_title')->toString() ?: null,
            'meta_description' => $this->string('meta_description')->toString() ?: null,
            'capacity' => $this->filled('capacity') ? (int) $this->input('capacity') : null,
            'is_active' => $this->boolean('is_active', true),
            'faqs' => collect($this->input('faqs', []))
                ->filter(fn ($faq) => filled($faq['question'] ?? null) || filled($faq['answer'] ?? null))
                ->values()
                ->all(),
        ];
    }

    public function galleryImages(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input('gallery_images')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function existingGalleryImages(): array
    {
        return collect($this->input('existing_gallery_images', []))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    public function ticketPayloads(): array
    {
        return collect($this->input('tickets', []))
            ->map(function ($ticket) {
                return [
                    'name' => trim((string) ($ticket['name'] ?? '')),
                    'type' => trim((string) ($ticket['type'] ?? 'regular')) ?: 'regular',
                    'price' => (float) ($ticket['price'] ?? 0),
                    'price_before_discount' => filled($ticket['price_before_discount'] ?? null) ? (float) $ticket['price_before_discount'] : null,
                    'quantity' => (int) ($ticket['quantity'] ?? 0),
                    'description' => trim((string) ($ticket['description'] ?? '')) ?: null,
                    'features' => collect(preg_split('/\r\n|\r|\n/', (string) ($ticket['features'] ?? '')))
                        ->map(fn ($line) => trim($line))
                        ->filter()
                        ->values()
                        ->all(),
                    'purchase_limit_per_user' => filled($ticket['purchase_limit_per_user'] ?? null) ? (int) $ticket['purchase_limit_per_user'] : null,
                    'label_color' => trim((string) ($ticket['label_color'] ?? '')) ?: null,
                    'sort_order' => (int) ($ticket['sort_order'] ?? 0),
                    'status' => trim((string) ($ticket['status'] ?? 'active')) ?: 'active',
                    'starts_at' => $ticket['starts_at'] ?? null,
                    'ends_at' => $ticket['ends_at'] ?? null,
                    'is_active' => filter_var($ticket['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_hidden' => filter_var($ticket['is_hidden'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'uses_qr' => filter_var($ticket['uses_qr'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(fn ($ticket) => filled($ticket['name']) && $ticket['price'] >= 0)
            ->values()
            ->all();
    }
}
