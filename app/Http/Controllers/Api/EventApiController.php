<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventApiController extends Controller
{
    public function index()
    {
        return Event::with(['city', 'category', 'tickets'])->published()->paginate(10);
    }

    public function show(Event $event)
    {
        return $event->load(['city', 'category', 'images', 'tickets']);
    }
}
