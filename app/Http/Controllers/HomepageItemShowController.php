<?php

namespace App\Http\Controllers;

use App\Models\HomepageItem;

class HomepageItemShowController extends Controller
{
    public function __invoke(HomepageItem $homepageItem)
    {
        $homepageItem->load([
            'category',
            'city',
            'event.tickets' => fn ($query) => $query->visible()->orderBy('sort_order'),
        ]);

        $resolvedEvent = $homepageItem->resolvedEvent();
        if ($resolvedEvent && ! $homepageItem->relationLoaded('event')) {
            $homepageItem->setRelation('event', $resolvedEvent);
        } elseif ($resolvedEvent && ! $homepageItem->event) {
            $homepageItem->setRelation('event', $resolvedEvent);
        }

        $relatedItems = HomepageItem::active()
            ->where('id', '!=', $homepageItem->id)
            ->where(function ($query) use ($homepageItem) {
                $query->where('section_key', $homepageItem->section_key);

                if ($homepageItem->category_id) {
                    $query->orWhere('category_id', $homepageItem->category_id);
                }
            })
            ->take(4)
            ->get();

        $defaultTicket = $resolvedEvent?->tickets->first(
            fn ($ticket) => $ticket->isReservableForDate(1, optional($resolvedEvent->start_date)->format('Y-m-d'))
        );
        $quickCheckoutUrl = null;

        if ($resolvedEvent && $defaultTicket) {
            $quickCheckoutUrl = route('bookings.create', $resolvedEvent).'?'.http_build_query([
                'booking_date' => optional($resolvedEvent->start_date)->format('Y-m-d') ?: now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'quantities' => [
                    $defaultTicket->id => 1,
                ],
            ]);
        }

        return view('homepage-items.show', [
            'item' => $homepageItem,
            'relatedItems' => $relatedItems,
            'quickCheckoutUrl' => $quickCheckoutUrl,
            'defaultTicket' => $defaultTicket,
            'resolvedEvent' => $resolvedEvent,
        ]);
    }
}
