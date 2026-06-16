<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SupportConversationResource;
use App\Http\Resources\Api\SupportMessageResource;
use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SupportConversationController extends Controller
{
    public function current(Request $request)
    {
        $conversation = SupportConversation::query()
            ->with(['messages.sender'])
            ->where('access_token', $request->query('token'))
            ->firstOrFail();

        return new SupportConversationResource($conversation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9_]+$/'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'topic' => ['nullable', 'string', Rule::in(['community', 'booking', 'payment', 'technical', 'other'])],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $conversation = SupportConversation::create([
            ...$validated,
            'topic' => $validated['topic'] ?? 'community',
            'user_id' => optional($request->user('sanctum'))->id,
            'access_token' => Str::random(56),
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_type' => 'customer',
            'sender_id' => optional($request->user('sanctum'))->id,
            'body' => $validated['message'] ?? 'مرحباً، أريد فتح محادثة مع فريق الدعم.',
        ]);

        return (new SupportConversationResource($conversation->load(['messages.sender'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, SupportConversation $conversation)
    {
        $this->authorizeToken($request, $conversation);

        return new SupportConversationResource($conversation->load(['messages.sender']));
    }

    public function message(Request $request, SupportConversation $conversation)
    {
        $this->authorizeToken($request, $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'customer',
            'sender_id' => optional($request->user('sanctum'))->id,
            'body' => $validated['body'],
        ]);

        $conversation->update([
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        return (new SupportMessageResource($message->load('sender')))
            ->response()
            ->setStatusCode(201);
    }

    private function authorizeToken(Request $request, SupportConversation $conversation): void
    {
        abort_unless(hash_equals($conversation->access_token, (string) $request->input('token')), 403);
    }
}
