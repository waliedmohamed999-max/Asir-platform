<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\CityResource;
use App\Http\Resources\Api\HomepageItemResource;
use App\Http\Resources\Api\VenueResource;
use App\Models\Category;
use App\Models\City;
use App\Models\HomepageItem;
use App\Models\Venue;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories()
    {
        return CategoryResource::collection(Category::where('is_active', true)->orderBy('sort_order')->get());
    }

    public function cities()
    {
        return CityResource::collection(City::where('is_active', true)->orderBy('sort_order')->get());
    }

    public function offers(Request $request)
    {
        return $this->homepageSection($request, ['offers', 'discounts']);
    }

    public function services(Request $request)
    {
        return $this->homepageSection($request, ['services']);
    }

    public function venues(Request $request)
    {
        $venues = Venue::with('city')
            ->where('is_active', true)
            ->when($request->filled('city_id'), fn ($query) => $query->where('city_id', $request->integer('city_id')))
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 12));

        return VenueResource::collection($venues);
    }

    private function homepageSection(Request $request, array $sections)
    {
        $items = HomepageItem::with(['event.city', 'event.category', 'event.tickets'])
            ->active()
            ->whereIn('section_key', $sections)
            ->when($request->filled('city_id'), fn ($query) => $query->where('city_id', $request->integer('city_id')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->paginate($request->integer('per_page', 12));

        return HomepageItemResource::collection($items);
    }
}
