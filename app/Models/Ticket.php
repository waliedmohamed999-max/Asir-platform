<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'type',
        'price',
        'price_before_discount',
        'quantity',
        'description',
        'features',
        'purchase_limit_per_user',
        'label_color',
        'sort_order',
        'uses_qr',
        'is_hidden',
        'status',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_before_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'uses_qr' => 'boolean',
            'is_hidden' => 'boolean',
            'features' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_hidden', false)
            ->where('status', '!=', 'inactive');
    }

    public function scopeSellable(Builder $query): Builder
    {
        return $query->visible()
            ->where(function (Builder $builder) {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $builder) {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function isSellableForQuantity(int $quantity): bool
    {
        if (! $this->is_active || $this->is_hidden || $this->status === 'inactive' || $this->status === 'sold_out') {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->remaining_quantity < $quantity) {
            return false;
        }

        if ($this->purchase_limit_per_user && $quantity > $this->purchase_limit_per_user) {
            return false;
        }

        return true;
    }

    public function isReservableForDate(int $quantity, string|\DateTimeInterface|null $bookingDate = null): bool
    {
        if (! $this->is_active || $this->is_hidden || $this->status === 'inactive' || $this->status === 'sold_out') {
            return false;
        }

        if ($this->remaining_quantity < $quantity) {
            return false;
        }

        if ($this->purchase_limit_per_user && $quantity > $this->purchase_limit_per_user) {
            return false;
        }

        if (! $bookingDate) {
            return true;
        }

        $date = Carbon::parse($bookingDate);

        if ($this->starts_at && $date->lt($this->starts_at->copy()->startOfDay())) {
            return false;
        }

        if ($this->ends_at && $date->gt($this->ends_at->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function getSoldQuantityAttribute(): int
    {
        return (int) $this->bookingItems()->sum('quantity');
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - $this->sold_quantity);
    }
}
