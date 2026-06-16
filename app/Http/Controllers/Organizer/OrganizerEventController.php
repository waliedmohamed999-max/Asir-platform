<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventUpsertRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Services\Admin\EventAdminService;
use Illuminate\Http\Request;

class OrganizerEventController extends Controller
{
    public function __construct(private readonly EventAdminService $eventAdminService)
    {
    }

    public function index(Request $request)
    {
        $events = Event::with('tickets')
            ->where('organizer_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        return view('organizer.events.form', [
            'event' => new Event(),
            'cities' => City::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => ['draft', 'published', 'scheduled'],
            'ticketTypes' => ['regular', 'vip', 'vvip', 'early_bird', 'group', 'child', 'adult', 'ladies'],
        ]);
    }

    public function store(EventUpsertRequest $request)
    {
        $this->eventAdminService->store(
            $request->eventData(),
            $request->galleryImages(),
            $request->ticketPayloads(),
            $request->user()->id,
            $request->file('banner_image'),
            $request->file('gallery_files', [])
        );

        return redirect()->route('organizer.events.index')->with('success', 'تم إنشاء الفعالية.');
    }

    public function edit(Request $request, Event $event)
    {
        abort_unless($event->organizer_id === $request->user()->id, 403);
        $event->loadMissing(['images', 'tickets']);

        return view('organizer.events.form', [
            'event' => $event,
            'cities' => City::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => ['draft', 'published', 'scheduled'],
            'ticketTypes' => ['regular', 'vip', 'vvip', 'early_bird', 'group', 'child', 'adult', 'ladies'],
        ]);
    }

    public function update(EventUpsertRequest $request, Event $event)
    {
        abort_unless($event->organizer_id === $request->user()->id, 403);

        $this->eventAdminService->update(
            $event,
            $request->eventData(),
            $request->galleryImages(),
            $request->ticketPayloads(),
            $request->existingGalleryImages(),
            $request->file('banner_image'),
            $request->file('gallery_files', []),
            $request->boolean('remove_banner_image')
        );

        return back()->with('success', 'تم تحديث الفعالية.');
    }
}
