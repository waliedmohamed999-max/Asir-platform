<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Coupon;
use Livewire\Component;

class TicketSelector extends Component
{
    public Event $event;
    public $ticketTypeId;
    public $quantity = 1;
    public $promoCode;
    public $total = 0;
    public $discount = 0;
    public $final = 0;

    public function mount()
    {
        $this->ticketTypeId = $this->event->ticketTypes->first()->id;
        $this->recalculate();
    }

    public function updated($field)
    {
        $this->recalculate();
    }

    public function recalculate()
    {
        $ticket = $this->event->ticketTypes->find($this->ticketTypeId);
        if (!$ticket) {
            return;
        }

        $this->quantity = max(1, min($ticket->max_quantity, (int)$this->quantity));
        $this->total = $ticket->price * $this->quantity;
        $this->discount = 0;
        $this->final = $this->total;

        if ($this->promoCode) {
            $coupon = Coupon::where('code', $this->promoCode)->first();
            if ($coupon && $coupon->isValid()) {
                $this->discount = $coupon->type === 'percent' ? round($this->total * ($coupon->value / 100), 2) : min($coupon->value, $this->total);
                $this->final = $this->total - $this->discount;
            }
        }
    }

    public function render()
    {
        return view('livewire.ticket-selector', ['ticketTypes' => $this->event->ticketTypes]);
    }
}
