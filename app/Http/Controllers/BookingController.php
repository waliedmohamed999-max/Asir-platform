<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function create(Request $request, Event $event)
    {
        $checkout = $this->bookingService->prepareCheckoutFromRequest($request, $event);

        return view('bookings.create', [
            'event' => $event->load(['city', 'category']),
            'checkout' => $checkout,
            'validated' => $checkout['validated'],
        ]);
    }

    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['event', 'items.ticket', 'latestPayment'])
            ->latest()
            ->paginate(10);

        return view('dashboard.bookings', compact('bookings'));
    }

    public function show(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load(['event.city', 'event.category', 'items.ticket', 'latestPayment']);

        return view('bookings.show', compact('booking'));
    }

    public function pdf(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load(['event.city', 'event.category', 'items.ticket', 'latestPayment']);

        return Pdf::loadView('bookings.pdf', [
            'booking' => $booking,
        ])->setPaper('a4', 'portrait')->download("ticket-{$booking->reference}.pdf");
    }

    public function store(Request $request, Event $event)
    {
        $booking = $this->bookingService->createFromRequest($request, $event);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'تم تأكيد الحجز وإصدار التذاكر بنجاح.');
    }
}
