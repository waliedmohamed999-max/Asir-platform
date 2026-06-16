<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function index(Request $request)
    {
        $bookings = Booking::with(['event.city', 'event.category', 'items.activeResaleListing', 'latestPayment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return BookingResource::collection($bookings);
    }

    public function store(Request $request)
    {
        $event = Event::published()->where('is_active', true)->findOrFail($request->integer('event_id'));
        $booking = $this->bookingService->createFromRequest($request, $event);

        return (new BookingResource($booking->load(['event.city', 'event.category', 'items', 'latestPayment'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        return new BookingResource($booking->load(['event.city', 'event.category', 'items.activeResaleListing', 'latestPayment']));
    }
}
