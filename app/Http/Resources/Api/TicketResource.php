<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = (float) $this->price;
        $beforeDiscount = $this->price_before_discount ? (float) $this->price_before_discount : null;
        $discountPercent = $beforeDiscount && $beforeDiscount > $price
            ? (int) round((($beforeDiscount - $price) / $beforeDiscount) * 100)
            : 0;
        $remaining = $this->remaining_quantity;
        $sold = $this->sold_quantity;
        $isAvailable = $this->isSellableForQuantity(1);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'price' => $price,
            'price_label' => number_format($price, 2).' SAR',
            'price_before_discount' => $beforeDiscount,
            'price_before_discount_label' => $beforeDiscount ? number_format($beforeDiscount, 2).' SAR' : null,
            'discount_percent' => $discountPercent,
            'currency' => 'SAR',
            'description' => $this->description,
            'features' => $this->features ?? [],
            'sold_quantity' => $sold,
            'remaining_quantity' => $remaining,
            'capacity' => (int) $this->quantity,
            'purchase_limit_per_user' => $this->purchase_limit_per_user,
            'label_color' => $this->label_color ?: '#7C3AED',
            'status' => $this->status,
            'is_available' => $isAvailable,
            'availability_label' => $isAvailable
                ? ($remaining <= 10 ? 'Limited seats' : 'Available')
                : ($this->status === 'sold_out' || $remaining <= 0 ? 'Sold out' : 'Unavailable'),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
        ];
    }
}
