<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'city_id',
        'logo_url',
        'bio',
        'whatsapp',
        'instagram_url',
        'x_url',
        'website_url',
        'role',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public static function availableRoles(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'event_manager' => 'Event Manager',
            'content_manager' => 'Content Manager',
            'finance' => 'Finance',
            'support' => 'Support',
            'organizer' => 'Organizer',
            'customer' => 'Customer',
        ];
    }

    public static function adminRoles(): array
    {
        return ['super_admin', 'admin', 'event_manager', 'content_manager', 'finance', 'support'];
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', self::adminRoles());
    }

    public function scopeOrganizers(Builder $query): Builder
    {
        return $query->where('role', 'organizer');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }
}
