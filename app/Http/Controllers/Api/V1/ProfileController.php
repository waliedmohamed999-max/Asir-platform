<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function favorites(Request $request)
    {
        $events = Event::with(['city', 'category', 'tickets'])
            ->withMin('tickets', 'price')
            ->whereIn('id', DB::table('favorite_events')->where('user_id', $request->user()->id)->pluck('event_id'))
            ->paginate($request->integer('per_page', 12));

        return EventResource::collection($events);
    }

    public function toggleFavorite(Request $request, Event $event)
    {
        $userId = $request->user()->id;
        $exists = DB::table('favorite_events')->where(['user_id' => $userId, 'event_id' => $event->id])->exists();

        if ($exists) {
            DB::table('favorite_events')->where(['user_id' => $userId, 'event_id' => $event->id])->delete();
        } else {
            DB::table('favorite_events')->insert([
                'user_id' => $userId,
                'event_id' => $event->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['is_favorite' => ! $exists]);
    }

    public function notifications(Request $request)
    {
        return response()->json([
            'data' => $request->user()->notifications()->latest()->limit(50)->get()->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->data['title'] ?? null,
                'body' => $notification->data['body'] ?? null,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function registerDevice(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'max:30'],
        ]);

        DB::table('device_tokens')->updateOrInsert(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? null,
                'last_seen_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'Device registered']);
    }
}
