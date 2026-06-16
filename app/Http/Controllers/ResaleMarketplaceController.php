<?php

namespace App\Http\Controllers;

use App\Models\ResaleListing;

class ResaleMarketplaceController extends Controller
{
    public function __invoke()
    {
        $listings = ResaleListing::with(['event.city', 'event.category', 'event.images', 'ticket', 'seller', 'bookingItem'])
            ->visible()
            ->latest('listed_at')
            ->paginate(12);

        return view('resale.index', compact('listings'));
    }
}
