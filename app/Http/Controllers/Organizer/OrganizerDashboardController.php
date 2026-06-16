<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizerDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $organizer = $request->user();
        $eventsBaseQuery = Event::query()->where('organizer_id', $organizer->id);

        $events = Event::query()
            ->withCount('bookings')
            ->withSum(['bookings as paid_revenue' => fn ($query) => $query->where('payment_status', 'paid')], 'total_amount')
            ->where('organizer_id', $organizer->id)
            ->latest()
            ->paginate(6);

        $recentBookings = Booking::query()
            ->with(['user', 'event', 'latestPayment'])
            ->whereHas('event', fn ($query) => $query->where('organizer_id', $organizer->id))
            ->latest()
            ->take(5)
            ->get();

        $soldTickets = BookingItem::query()
            ->whereHas('booking.event', fn ($query) => $query->where('organizer_id', $organizer->id))
            ->sum('quantity');

        $revenueByStatus = Booking::query()
            ->select('payment_status', DB::raw('SUM(total_amount) as total'))
            ->whereHas('event', fn ($query) => $query->where('organizer_id', $organizer->id))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $stats = [
            'events_count' => (clone $eventsBaseQuery)->count(),
            'published_events' => (clone $eventsBaseQuery)->where('status', 'published')->count(),
            'draft_events' => (clone $eventsBaseQuery)->where('status', 'draft')->count(),
            'upcoming_events' => (clone $eventsBaseQuery)->where('start_date', '>=', now())->count(),
            'bookings_count' => Booking::query()->whereHas('event', fn ($query) => $query->where('organizer_id', $organizer->id))->count(),
            'paid_bookings' => Booking::query()->whereHas('event', fn ($query) => $query->where('organizer_id', $organizer->id))->where('payment_status', 'paid')->count(),
            'sold_tickets' => (int) $soldTickets,
            'gross_revenue' => (float) ($revenueByStatus['paid'] ?? 0),
            'pending_revenue' => (float) ($revenueByStatus['pending'] ?? 0),
        ];

        return view('organizer.dashboard', compact('events', 'stats', 'organizer', 'recentBookings'));
    }
}
