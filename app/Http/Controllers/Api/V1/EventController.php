<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()
            ->with(['city', 'category', 'tickets' => fn ($tickets) => $tickets->visible()->orderBy('sort_order')])
            ->withMin(['tickets' => fn ($tickets) => $tickets->visible()], 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->whereDate('start_date', '>=', now()->startOfDay())
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                ->where('title', 'like', '%'.$request->string('q').'%')
                ->orWhere('title_en', 'like', '%'.$request->string('q').'%')
                ->orWhere('venue_name', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('city_id'), fn ($query) => $query->where('city_id', $request->integer('city_id')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $request->string('category'))))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('start_date', $request->date('date')))
            ->when($request->filled('min_price'), fn ($query) => $query->whereHas('tickets', fn ($tickets) => $tickets->where('price', '>=', $request->float('min_price'))))
            ->when($request->filled('max_price'), fn ($query) => $query->whereHas('tickets', fn ($tickets) => $tickets->where('price', '<=', $request->float('max_price'))))
            ->orderBy('start_date')
            ->paginate($request->integer('per_page', 12));

        return EventResource::collection($events);
    }

    public function show(Event $event)
    {
        abort_unless($event->status === 'published', 404);

        return new EventResource(
            $event->load(['city', 'category', 'organizer', 'images'])
                ->load(['tickets' => fn ($tickets) => $tickets->visible()->orderBy('sort_order')])
                ->loadAvg('reviews', 'rating')
                ->loadCount('reviews')
        );
    }
}
