<?php

namespace App\Services;

use App\Mail\TicketsBookedMail;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingService
{
    public function prepareCheckoutFromRequest(Request $request, Event $event, bool $strict = false): array
    {
        $validated = $request->validate([
            'booking_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:stripe,paypal,mada,cash'],
            'promo_code' => ['nullable', 'string'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->prepareCheckout($validated, $event, $strict);
    }

    public function createFromRequest(Request $request, Event $event): Booking
    {
        $customer = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $checkout = $this->prepareCheckoutFromRequest($request, $event, true);
        $validated = $checkout['validated'];
        $coupon = $checkout['coupon'];
        $subtotal = $checkout['subtotal'];
        $discount = $checkout['discount'];
        $total = $checkout['total'];
        $items = $checkout['items'];

        $booking = DB::transaction(function () use ($request, $event, $validated, $items, $coupon, $subtotal, $discount, $total, $customer) {
            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'event_id' => $event->id,
                'coupon_id' => $coupon?->id,
                'reference' => 'FRH-'.strtoupper(Str::random(8)),
                'status' => 'paid',
                'payment_status' => 'paid',
                'booking_date' => $validated['booking_date'],
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'customer_email' => $customer['customer_email'],
                'customer_phone' => $customer['customer_phone'] ?? null,
            ]);

            foreach ($items as $item) {
                $booking->items()->create([
                    'ticket_id' => $item['ticket']->id,
                    'ticket_name' => $item['ticket']->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['ticket']->price,
                    'line_total' => $item['line_total'],
                    'attendee_date' => $validated['booking_date'],
                    'qr_token' => (string) Str::uuid(),
                ]);
            }

            $booking->payment()->create([
                'gateway' => $validated['payment_method'],
                'transaction_reference' => strtoupper($validated['payment_method']).'-'.Str::upper(Str::random(10)),
                'amount' => $total,
                'currency' => 'SAR',
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => [
                    'mode' => 'demo',
                    'note' => 'Gateway placeholders are ready for real credentials.',
                ],
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $request->user()->forceFill([
                'name' => $customer['customer_name'],
                'email' => $customer['customer_email'],
                'phone' => $customer['customer_phone'] ?? $request->user()->phone,
            ])->save();

            return $booking->load(['items.ticket', 'event', 'latestPayment']);
        });

        if (filled(config('mail.from.address'))) {
            Mail::to($booking->customer_email)->queue(new TicketsBookedMail($booking));
        }

        return $booking;
    }

    private function calculateDiscount(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon) {
            return 0;
        }

        return $coupon->type === 'percentage'
            ? round($subtotal * ($coupon->value / 100), 2)
            : min($subtotal, (float) $coupon->value);
    }

    private function prepareCheckout(array $validated, Event $event, bool $strict = false): array
    {
        $visibleTickets = Ticket::where('event_id', $event->id)
            ->visible()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('id');

        $selectedQuantities = collect($validated['quantities'])
            ->mapWithKeys(fn ($quantity, $ticketId) => [(int) $ticketId => (int) $quantity])
            ->filter(fn ($quantity) => $quantity > 0);

        $ticketIds = $selectedQuantities->keys()->all();
        $tickets = $visibleTickets
            ->filter(fn (Ticket $ticket) => in_array($ticket->id, $ticketIds, true))
            ->keyBy('id');

        if (! $strict) {
            $selectedQuantities = $selectedQuantities->filter(function (int $quantity, int $ticketId) use ($tickets, $validated) {
                $ticket = $tickets->get($ticketId);

                return $ticket && $ticket->isReservableForDate($quantity, $validated['booking_date'] ?? null);
            });

            if ($selectedQuantities->isEmpty()) {
                $fallbackTicket = $visibleTickets->first(fn (Ticket $ticket) => $ticket->isReservableForDate(1, $validated['booking_date'] ?? null));

                if ($fallbackTicket) {
                    $selectedQuantities = collect([$fallbackTicket->id => 1]);
                    $tickets = $visibleTickets
                        ->filter(fn (Ticket $ticket) => $ticket->id === $fallbackTicket->id)
                        ->keyBy('id');
                    $validated['quantities'] = [$fallbackTicket->id => 1];
                }
            }
        }

        abort_if($selectedQuantities->isEmpty(), 422, 'الرجاء اختيار تذكرة واحدة على الأقل.');

        $coupon = null;
        $subtotal = 0;
        $items = [];

        foreach ($selectedQuantities as $ticketId => $quantity) {
            $ticket = $tickets->get($ticketId);
            abort_unless($ticket, 422, 'تم اختيار نوع تذكرة غير صالح.');
            abort_unless($ticket->isReservableForDate((int) $quantity, $validated['booking_date'] ?? null), 422, 'إحدى التذاكر المختارة غير متاحة للبيع حالياً أو الكمية المطلوبة غير متوفرة.');

            $lineTotal = $ticket->price * $quantity;
            $subtotal += $lineTotal;
            $items[] = ['ticket' => $ticket, 'quantity' => $quantity, 'line_total' => $lineTotal];
        }

        $validated['quantities'] = $selectedQuantities->all();

        if (! empty($validated['promo_code'])) {
            $coupon = Coupon::active()->where('code', strtoupper($validated['promo_code']))->first();
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);
        $total = max($subtotal - $discount, 0);

        return compact('validated', 'tickets', 'coupon', 'subtotal', 'discount', 'total', 'items');
    }
}
