<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportConversationManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $conversations = SupportConversation::query()
            ->with(['assignee'])
            ->withCount('messages')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where(function ($builder) use ($search) {
                $builder->where('username', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            }))
            ->orderByRaw('CASE WHEN status = "open" THEN 0 WHEN status = "pending" THEN 1 ELSE 2 END')
            ->latest('last_message_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'open' => SupportConversation::where('status', 'open')->count(),
            'pending' => SupportConversation::where('status', 'pending')->count(),
            'closed' => SupportConversation::where('status', 'closed')->count(),
            'total' => SupportConversation::count(),
        ];

        return view('admin.support-conversations.index', compact('conversations', 'stats', 'status', 'search'));
    }

    public function show(SupportConversation $supportConversation)
    {
        $supportConversation->load(['messages.sender', 'assignee']);

        return view('admin.support-conversations.show', [
            'conversation' => $supportConversation,
        ]);
    }

    public function reply(Request $request, SupportConversation $supportConversation)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $supportConversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $supportConversation->update([
            'assigned_to' => $supportConversation->assigned_to ?: $request->user()->id,
            'status' => 'pending',
            'last_message_at' => now(),
        ]);

        return back()->with('success', 'تم إرسال الرد إلى التطبيق.');
    }

    public function update(Request $request, SupportConversation $supportConversation)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'pending', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
        ]);

        $supportConversation->update($validated + [
            'assigned_to' => $supportConversation->assigned_to ?: $request->user()->id,
        ]);

        return back()->with('success', 'تم تحديث حالة المحادثة.');
    }
}
