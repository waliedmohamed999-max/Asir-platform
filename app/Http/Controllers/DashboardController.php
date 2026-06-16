<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->loadCount('bookings');
        $bookingsQuery = $user->bookings();

        $recentBookings = (clone $bookingsQuery)
            ->with(['event.city', 'latestPayment'])
            ->latest()
            ->take(5)
            ->get();

        $ticketsBooked = BookingItem::query()
            ->whereHas('booking', fn ($query) => $query->where('user_id', $user->id)->where('payment_status', 'paid'))
            ->sum('quantity');

        $upcomingEventsCount = Booking::query()
            ->where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereHas('event', fn ($query) => $query->where('start_date', '>=', now()))
            ->distinct('event_id')
            ->count('event_id');

        $favoriteCity = Booking::query()
            ->select('cities.name', DB::raw('COUNT(*) as aggregate'))
            ->join('events', 'events.id', '=', 'bookings.event_id')
            ->join('cities', 'cities.id', '=', 'events.city_id')
            ->where('bookings.user_id', $user->id)
            ->groupBy('cities.name')
            ->orderByDesc('aggregate')
            ->value('cities.name');

        $recentPayments = Payment::query()
            ->with('booking.event')
            ->whereHas('booking', fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->take(4)
            ->get();

        $stats = [
            'paid_bookings' => (clone $bookingsQuery)->where('payment_status', 'paid')->count(),
            'pending_bookings' => (clone $bookingsQuery)->where('payment_status', 'pending')->count(),
            'refunded_bookings' => (clone $bookingsQuery)->where('payment_status', 'refunded')->count(),
            'spent_total' => (clone $bookingsQuery)->where('payment_status', 'paid')->sum('total_amount'),
            'tickets_booked' => (int) $ticketsBooked,
            'upcoming_events' => (int) $upcomingEventsCount,
            'favorite_city' => $favoriteCity,
        ];

        return view('dashboard.index', compact('user', 'recentBookings', 'recentPayments', 'stats'));
    }
}
