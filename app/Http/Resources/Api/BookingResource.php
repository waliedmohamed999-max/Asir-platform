<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'booking_date' => $this->booking_date?->toIso8601String(),
            'subtotal_amount' => (float) $this->subtotal_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'currency' => 'SAR',
            'event' => new EventResource($this->whenLoaded('event')),
            'payment' => $this->whenLoaded('latestPayment'),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'ticket_name' => $item->ticket_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'qr_token' => $item->qr_token,
                'resale_listing' => $item->relationLoaded('activeResaleListing') && $item->activeResaleListing ? [
                    'id' => $item->activeResaleListing->id,
                    'reference' => $item->activeResaleListing->reference,
                    'price' => (float) $item->activeResaleListing->price,
                    'status' => $item->activeResaleListing->status,
                ] : null,
            ])->values()),
        ];
    }
}
