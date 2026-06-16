<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomepageItem extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'image_url',
        'hero_image_url',
        'gallery',
        'cta_label',
        'cta_url',
        'open_in_new_tab',
        'section_key',
        'ad_type',
        'content_type',
        'category_id',
        'city_id',
        'event_id',
        'venue_name',
        'date_label',
        'description',
        'includes',
        'terms',
        'schedule',
        'directions',
        'location_title',
        'location_code',
        'map_url',
        'price_label',
        'meta_label',
        'badge',
        'rating',
        'sort_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'gallery' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (HomepageItem $item) {
            if (blank($item->slug) && filled($item->title)) {
                $item->slug = Str::slug($item->title).'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $builder) {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $builder) {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function resolvedEvent(): ?Event
    {
        if ($this->relationLoaded('event') && $this->event) {
            return $this->event;
        }

        if ($this->event_id && $this->event()->exists()) {
            return $this->event()->first();
        }

        $query = Event::query()
            ->with(['tickets' => fn ($ticketQuery) => $ticketQuery->visible()->orderBy('sort_order')])
            ->published()
            ->where('is_active', true)
            ->whereHas('tickets', fn ($ticketQuery) => $ticketQuery->visible());

        if ($this->city_id) {
            $query->where('city_id', $this->city_id);
        }

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        $candidates = $query
            ->orderBy('start_date')
            ->limit(12)
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $matched = $candidates
            ->map(fn (Event $event) => ['event' => $event, 'score' => $this->eventMatchScore($event)])
            ->sortByDesc('score')
            ->values();

        return ($matched->first()['score'] ?? 0) > 0 ? $matched->first()['event'] : null;
    }

    private function eventMatchScore(Event $event): int
    {
        $score = 0;

        $itemTitle = $this->normalizedComparisonText($this->title);
        $eventTitle = $this->normalizedComparisonText($event->title);
        $eventTitleEn = $this->normalizedComparisonText($event->title_en);
        $itemVenue = $this->normalizedComparisonText($this->venue_name);
        $eventVenue = $this->normalizedComparisonText($event->venue_name);

        if ($itemTitle !== '' && ($itemTitle === $eventTitle || $itemTitle === $eventTitleEn)) {
            $score += 120;
        } elseif (
            $itemTitle !== ''
            && (
                ($eventTitle !== '' && str_contains($itemTitle, $eventTitle))
                || ($eventTitle !== '' && str_contains($eventTitle, $itemTitle))
                || ($eventTitleEn !== '' && str_contains($itemTitle, $eventTitleEn))
                || ($eventTitleEn !== '' && str_contains($eventTitleEn, $itemTitle))
            )
        ) {
            $score += 70;
        }

        if ($this->category_id && $this->category_id === $event->category_id) {
            $score += 25;
        }

        if ($this->city_id && $this->city_id === $event->city_id) {
            $score += 25;
        }

        if ($itemVenue !== '' && $eventVenue !== '' && $itemVenue === $eventVenue) {
            $score += 20;
        }

        if ($this->date_label && $event->start_date?->translatedFormat('d F') && str_contains($this->date_label, $event->start_date->translatedFormat('d'))) {
            $score += 10;
        }

        return $score;
    }

    private function normalizedComparisonText(?string $value): string
    {
        $text = Str::of((string) $value)
            ->lower()
            ->replace(['-', '_'], ' ')
            ->squish()
            ->toString();

        return trim($text);
    }
}
