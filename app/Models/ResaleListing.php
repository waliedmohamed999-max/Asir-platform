<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResaleListing extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SOLD = 'sold';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'booking_item_id',
        'seller_id',
        'buyer_id',
        'event_id',
        'ticket_id',
        'reference',
        'price',
        'currency',
        'status',
        'listed_at',
        'sold_at',
        'expires_at',
        'seller_note',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'listed_at' => 'datetime',
            'sold_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $builder) {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->live()->whereHas('event', fn (Builder $event) => $event->published()->where('is_active', true));
    }
}
