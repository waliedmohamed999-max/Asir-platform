<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'city_id',
        'category_id',
        'title',
        'title_ar',
        'title_en',
        'slug',
        'excerpt',
        'description',
        'description_ar',
        'description_en',
        'venue_name',
        'venue_name_en',
        'map_url',
        'banner_image_url',
        'video_url',
        'location_lat',
        'location_lng',
        'terms',
        'refund_policy',
        'schedule_notes',
        'start_date',
        'end_date',
        'status',
        'is_featured',
        'show_on_homepage',
        'display_order',
        'faqs',
        'meta_title',
        'meta_description',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_featured' => 'boolean',
            'show_on_homepage' => 'boolean',
            'is_active' => 'boolean',
            'faqs' => 'array',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)->orderBy('sort_order');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->orderBy('sort_order');
    }

    public function ticketTypes(): HasMany
    {
        return $this->tickets();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EventReview::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getStartingPriceAttribute(): string
    {
        $price = $this->tickets->where('is_active', true)->where('is_hidden', false)->min('price') ?? 0;

        return number_format((float) $price, 2);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->images->first()?->image_url ?: $this->banner_image_url;
    }
}
