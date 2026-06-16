<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Models\HomepageItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $query = Event::query()
                ->with(['city', 'category', 'images', 'tickets'])
                ->where('status', 'published')
                ->whereDate('start_date', '>=', now()->startOfDay());

            if ($request->filled('city')) {
                $query->whereHas('city', fn ($cityQuery) => $cityQuery->where('slug', $request->string('city')));
            }

            if ($request->filled('category')) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $request->string('category')));
            }

            if ($request->filled('date')) {
                $range = $this->resolveDateFilter($request->string('date')->toString());

                if ($range) {
                    [$from, $to] = $range;
                    $query->whereBetween('start_date', [$from, $to]);
                }
            }

            $featuredEvents = (clone $query)
                ->where('is_featured', true)
                ->orderBy('start_date')
                ->take(3)
                ->get();

            $events = $query
                ->latest('is_featured')
                ->orderBy('start_date')
                ->paginate(9)
                ->withQueryString();

            return view('home', [
                'events' => $events,
                'featuredEvents' => $featuredEvents,
                'cities' => City::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
                'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
                'homepageSections' => HomepageItem::with(['category', 'city', 'event'])->active()->get()->groupBy('section_key'),
                'selectedDate' => $request->string('date')->toString(),
            ]);
        } catch (Throwable $exception) {
            return response()->view('vercel-fallback', [
                'reason' => config('app.debug') ? $exception->getMessage() : 'Database is not configured yet.',
            ], 200);
        }
    }

    private function resolveDateFilter(string $filter): ?array
    {
        return match ($filter) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'tomorrow' => [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()],
            'weekend' => [Carbon::now()->next(Carbon::FRIDAY)->startOfDay(), Carbon::now()->next(Carbon::SATURDAY)->endOfDay()],
            default => null,
        };
    }
}
