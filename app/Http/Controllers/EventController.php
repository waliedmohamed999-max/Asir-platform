<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Event;

class EventController extends Controller
{
    public function show(Event $event)
    {
        $event->load(['city', 'category', 'images', 'tickets' => fn ($query) => $query->orderBy('price')]);

        return view('events.show', [
            'event' => $event,
            'featuredCoupons' => Coupon::active()->take(2)->get(),
        ]);
    }
}
