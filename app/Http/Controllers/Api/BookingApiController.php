<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function store(Request $request)
    {
        $event = Event::findOrFail($request->integer('event_id'));
        $booking = $this->bookingService->createFromRequest($request, $event);

        return response()->json($booking->load(['items.ticket', 'latestPayment']), 201);
    }
}
