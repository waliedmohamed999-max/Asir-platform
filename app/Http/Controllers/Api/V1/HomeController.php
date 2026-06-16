<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\CityResource;
use App\Http\Resources\Api\EventResource;
use App\Http\Resources\Api\HomepageItemResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Models\HomepageItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke()
    {
        $lang = request('lang', 'ar');

        return response()->json(Cache::remember("api:v1:smart-home:$lang", now()->addMinutes(5), fn () => $this->payload()));
    }

    private function payload(): array
    {
        $items = HomepageItem::query()
            ->with(['event.city', 'event.category', 'event.tickets'])
            ->active()
            ->get()
            ->groupBy('section_key');

        $featuredEvents = Event::query()
            ->with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->where(fn ($query) => $query
                ->whereDate('start_date', '>=', now()->startOfDay())
                ->orWhereDate('end_date', '>=', now()->startOfDay()))
            ->where(fn ($query) => $query->where('is_featured', true)->orWhere('show_on_homepage', true))
            ->orderBy('display_order')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        $homeBanners = $this->homepageItems($items, ['hero', 'featured', 'hero_banners', 'homepage_hero']);
        $featuredItems = $this->homepageItems($items, ['featured_events', 'featured_tourism']);
        $todayItems = $this->homepageItems($items, ['today_events']);
        $nearbyItems = $this->homepageItems($items, ['nearby', 'nearby_entertainment']);
        $experienceItems = $this->homepageItems($items, ['nightlife', 'arabic_guide', 'theatre']);

        return [
            'banners' => HomepageItemResource::collection($homeBanners->take(10))->resolve(),
            'quick_actions' => [
                ['key' => 'nearby', 'label_ar' => 'قريبة منك', 'label_en' => 'Nearby', 'icon' => 'near_me'],
                ['key' => 'today', 'label_ar' => 'اليوم', 'label_en' => 'Today', 'icon' => 'calendar_today'],
                ['key' => 'offers', 'label_ar' => 'العروض', 'label_en' => 'Offers', 'icon' => 'local_offer'],
                ['key' => 'wallet', 'label_ar' => 'تذاكري', 'label_en' => 'Wallet', 'icon' => 'confirmation_number'],
            ],
            'trending_searches' => $this->trendingSearches(),
            'sections' => [
                'app_stories' => HomepageItemResource::collection($items->get('app_stories', collect()))->resolve(),
                'events' => EventResource::collection($featuredEvents)->resolve(),
                'recommended' => EventResource::collection($this->recommendedEvents())->resolve(),
                'trending' => EventResource::collection($this->trendingEvents())->resolve(),
                'upcoming' => EventResource::collection($this->upcomingEvents())->resolve(),
                'featured_events' => HomepageItemResource::collection($featuredItems)->resolve(),
                'featured_tourism' => HomepageItemResource::collection($items->get('featured_tourism', collect()))->resolve(),
                'today_cards' => HomepageItemResource::collection($todayItems)->resolve(),
                'experience_cards' => HomepageItemResource::collection($experienceItems)->resolve(),
                'offers' => HomepageItemResource::collection($items->get('offers', collect()))->resolve(),
                'services' => HomepageItemResource::collection($items->get('services', collect()))->resolve(),
                'places' => HomepageItemResource::collection($items->get('places', collect()))->resolve(),
                'most_requested' => HomepageItemResource::collection($items->get('most_requested', collect()))->resolve(),
                'nearby' => HomepageItemResource::collection($nearbyItems)->resolve(),
                'today' => EventResource::collection($this->todayEvents())->resolve(),
            ],
            'filters' => [
                'cities' => CityResource::collection(City::where('is_active', true)->orderBy('sort_order')->get())->resolve(),
                'categories' => CategoryResource::collection(Category::where('is_active', true)->orderBy('sort_order')->get())->resolve(),
            ],
        ];
    }

    private function homepageItems($groups, array $keys)
    {
        return collect($keys)
            ->flatMap(fn (string $key) => $groups->get($key, collect()))
            ->values();
    }

    private function todayEvents()
    {
        return Event::with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->whereDate('start_date', today())
            ->whereDate('start_date', today())
            ->orderBy('start_date')
            ->limit(12)
            ->get();
    }

    private function upcomingEvents()
    {
        return Event::with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->where(fn ($query) => $query
                ->where('start_date', '>=', now())
                ->orWhere('end_date', '>=', now()))
            ->orderBy('start_date')
            ->limit(12)
            ->get();
    }

    private function trendingEvents()
    {
        return Event::with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withCount('bookings')
            ->published()
            ->where(fn ($query) => $query
                ->whereDate('start_date', '>=', now()->startOfDay())
                ->orWhereDate('end_date', '>=', now()->startOfDay()))
            ->orderByDesc('bookings_count')
            ->orderByDesc('is_featured')
            ->limit(12)
            ->get();
    }

    private function recommendedEvents()
    {
        return Event::with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->where(fn ($query) => $query
                ->whereDate('start_date', '>=', now()->startOfDay())
                ->orWhereDate('end_date', '>=', now()->startOfDay()))
            ->orderByDesc('is_featured')
            ->orderBy('start_date')
            ->limit(12)
            ->get();
    }

    private function trendingSearches(): array
    {
        return DB::table('categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(8)
            ->pluck('name')
            ->all();
    }
}
