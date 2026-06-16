<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResaleListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'price' => (float) $this->price,
            'price_label' => number_format((float) $this->price, 2).' '.$this->currency,
            'currency' => $this->currency,
            'status' => $this->status,
            'seller_note' => $this->seller_note,
            'listed_at' => $this->listed_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'event' => new EventResource($this->whenLoaded('event')),
            'seller' => $this->whenLoaded('seller', fn () => [
                'id' => $this->seller?->id,
                'name' => $this->seller?->name,
            ]),
            'ticket' => $this->whenLoaded('ticket', fn () => [
                'id' => $this->ticket?->id,
                'name' => $this->ticket?->name ?? $this->bookingItem?->ticket_name,
                'type' => $this->ticket?->type,
                'label_color' => $this->ticket?->label_color,
            ]),
            'booking_item' => $this->whenLoaded('bookingItem', fn () => [
                'id' => $this->bookingItem?->id,
                'ticket_name' => $this->bookingItem?->ticket_name,
                'quantity' => $this->bookingItem?->quantity,
                'unit_price' => (float) ($this->bookingItem?->unit_price ?? 0),
                'qr_token' => $this->bookingItem?->qr_token,
            ]),
        ];
    }
}
