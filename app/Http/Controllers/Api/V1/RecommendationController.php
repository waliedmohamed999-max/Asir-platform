<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    public function __invoke(Request $request)
    {
        $categoryIds = $request->user()
            ? DB::table('user_interests')->where('user_id', $request->user()->id)->orderByDesc('weight')->pluck('category_id')->all()
            : [];

        $events = Event::query()
            ->with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->where('is_active', true)
            ->when($categoryIds !== [], fn ($query) => $query->orderByRaw('FIELD(category_id, '.implode(',', array_map('intval', $categoryIds)).') DESC'))
            ->orderByDesc('is_featured')
            ->orderBy('start_date')
            ->limit(20)
            ->get();

        return EventResource::collection($events);
    }
}
