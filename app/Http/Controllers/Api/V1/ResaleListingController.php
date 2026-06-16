<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ResaleListingResource;
use App\Models\BookingItem;
use App\Models\ResaleListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResaleListingController extends Controller
{
    public function index(Request $request)
    {
        $listings = ResaleListing::with(['event.city', 'event.category', 'event.tickets', 'ticket', 'seller', 'bookingItem'])
            ->visible()
            ->when($request->filled('event_id'), fn ($query) => $query->where('event_id', $request->integer('event_id')))
            ->latest('listed_at')
            ->paginate($request->integer('per_page', 12));

        return ResaleListingResource::collection($listings);
    }

    public function mine(Request $request)
    {
        $listings = ResaleListing::with(['event.city', 'event.category', 'ticket', 'bookingItem'])
            ->where('seller_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return ResaleListingResource::collection($listings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_item_id' => ['required', 'integer', 'exists:booking_items,id'],
            'price' => ['required', 'numeric', 'min:1', 'max:999999'],
            'seller_note' => ['nullable', 'string', 'max:500'],
        ]);

        $bookingItem = BookingItem::with(['booking.event', 'ticket', 'activeResaleListing'])
            ->findOrFail($validated['booking_item_id']);

        abort_unless($bookingItem->booking?->user_id === $request->user()->id, 403);

        if (! $this->isEligible($bookingItem)) {
            return response()->json([
                'message' => 'هذه التذكرة غير مؤهلة لإعادة البيع حالياً.',
            ], 422);
        }

        $listing = DB::transaction(function () use ($bookingItem, $validated, $request) {
            return ResaleListing::create([
                'booking_item_id' => $bookingItem->id,
                'seller_id' => $request->user()->id,
                'event_id' => $bookingItem->booking->event_id,
                'ticket_id' => $bookingItem->ticket_id,
                'reference' => 'RSL-'.Str::upper(Str::random(10)),
                'price' => $validated['price'],
                'currency' => 'SAR',
                'status' => ResaleListing::STATUS_ACTIVE,
                'listed_at' => now(),
                'expires_at' => optional($bookingItem->booking->event?->end_date)->subMinutes(30),
                'seller_note' => $validated['seller_note'] ?? null,
            ]);
        });

        return (new ResaleListingResource($listing->load(['event.city', 'event.category', 'ticket', 'bookingItem', 'seller'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, ResaleListing $resaleListing)
    {
        abort_unless($resaleListing->seller_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([ResaleListing::STATUS_CANCELLED])],
        ]);

        $resaleListing->update([
            'status' => $validated['status'],
        ]);

        return new ResaleListingResource($resaleListing->load(['event.city', 'event.category', 'ticket', 'bookingItem']));
    }

    private function isEligible(BookingItem $bookingItem): bool
    {
        $booking = $bookingItem->booking;

        if (! $booking || $booking->payment_status !== 'paid') {
            return false;
        }

        if (! in_array($booking->status, ['paid', 'confirmed', 'completed'], true)) {
            return false;
        }

        if ($bookingItem->activeResaleListing) {
            return false;
        }

        if ($booking->event?->end_date && $booking->event->end_date->isPast()) {
            return false;
        }

        return $bookingItem->quantity > 0;
    }
}
