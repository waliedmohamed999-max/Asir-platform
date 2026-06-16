<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\Event;
use Livewire\Component;

class EventBookingWidget extends Component
{
    public Event $event;

    public string $booking_date = '';

    public string $payment_method = 'stripe';

    public string $promo_code = '';

    public array $quantities = [];

    public function mount(Event $event): void
    {
        $this->event = $event->load([
            'tickets' => fn ($query) => $query->visible()->orderBy('sort_order'),
        ]);
        $this->booking_date = optional($event->start_date)->format('Y-m-d');

        foreach ($this->event->tickets as $ticket) {
            $this->quantities[$ticket->id] = 0;
        }
    }

    public function increment(int $ticketId): void
    {
        $ticket = $this->event->tickets->firstWhere('id', $ticketId);

        if (! $ticket) {
            return;
        }

        $current = (int) ($this->quantities[$ticketId] ?? 0);
        $maxAllowed = $ticket->purchase_limit_per_user
            ? min($ticket->remaining_quantity, $ticket->purchase_limit_per_user)
            : $ticket->remaining_quantity;

        $this->quantities[$ticketId] = min($current + 1, max(0, $maxAllowed));
    }

    public function decrement(int $ticketId): void
    {
        $this->quantities[$ticketId] = max(0, ($this->quantities[$ticketId] ?? 0) - 1);
    }

    public function getSubtotalProperty(): float
    {
        return (float) $this->event->tickets->sum(fn ($ticket) => $ticket->price * ($this->quantities[$ticket->id] ?? 0));
    }

    public function getDiscountProperty(): float
    {
        if (blank($this->promo_code)) {
            return 0;
        }

        $coupon = Coupon::active()->where('code', strtoupper($this->promo_code))->first();

        if (! $coupon) {
            return 0;
        }

        return $coupon->type === 'percentage'
            ? round($this->subtotal * ($coupon->value / 100), 2)
            : min($this->subtotal, (float) $coupon->value);
    }

    public function getTotalProperty(): float
    {
        return max($this->subtotal - $this->discount, 0);
    }

    public function render()
    {
        return view('livewire.event-booking-widget');
    }
}
