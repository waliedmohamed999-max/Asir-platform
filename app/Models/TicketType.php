<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'max_quantity',
        'description',
        'age_group',
        'is_vip',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
