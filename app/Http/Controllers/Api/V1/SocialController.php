<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    public function reviews(Event $event)
    {
        $reviews = DB::table('event_reviews')
            ->join('users', 'users.id', '=', 'event_reviews.user_id')
            ->where('event_reviews.event_id', $event->id)
            ->where('event_reviews.is_approved', true)
            ->latest('event_reviews.created_at')
            ->select('event_reviews.id', 'event_reviews.rating', 'event_reviews.comment', 'event_reviews.photos', 'event_reviews.created_at', 'users.name')
            ->paginate(12);

        return response()->json($reviews);
    }

    public function storeReview(Request $request, Event $event)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
        ]);

        DB::table('event_reviews')->updateOrInsert(
            ['user_id' => $request->user()->id, 'event_id' => $event->id],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'photos' => json_encode($validated['photos'] ?? []),
                'is_approved' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'Review saved']);
    }

    public function comments(Event $event)
    {
        $comments = DB::table('event_comments')
            ->join('users', 'users.id', '=', 'event_comments.user_id')
            ->where('event_comments.event_id', $event->id)
            ->where('event_comments.is_approved', true)
            ->latest('event_comments.created_at')
            ->select('event_comments.id', 'event_comments.body', 'event_comments.created_at', 'users.name')
            ->paginate(20);

        return response()->json($comments);
    }

    public function storeComment(Request $request, Event $event)
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:1000']]);

        DB::table('event_comments')->insert([
            'user_id' => $request->user()->id,
            'event_id' => $event->id,
            'body' => $validated['body'],
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Comment added'], 201);
    }

    public function followOrganizer(Request $request, User $organizer)
    {
        abort_unless($organizer->role === 'organizer', 404);

        DB::table('organizer_follows')->updateOrInsert(
            ['user_id' => $request->user()->id, 'organizer_id' => $organizer->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['is_following' => true]);
    }
}
