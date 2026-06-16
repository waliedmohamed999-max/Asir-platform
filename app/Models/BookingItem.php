<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'ticket_id',
        'ticket_name',
        'quantity',
        'unit_price',
        'line_total',
        'attendee_date',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'attendee_date' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function resaleListings(): HasMany
    {
        return $this->hasMany(ResaleListing::class);
    }

    public function activeResaleListing()
    {
        return $this->hasOne(ResaleListing::class)->whereIn('status', [
            ResaleListing::STATUS_ACTIVE,
            ResaleListing::STATUS_PENDING,
        ])->latestOfMany();
    }
}
