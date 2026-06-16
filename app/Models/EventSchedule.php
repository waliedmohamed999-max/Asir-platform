<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    use HasFactory;

    protected $dates = ['date', 'start_time', 'end_time'];

    protected $fillable = [
        'event_id',
        'date',
        'start_time',
        'end_time',
        'description',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
