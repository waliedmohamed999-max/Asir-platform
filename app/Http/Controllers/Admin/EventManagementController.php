<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EventUpsertRequest;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\EventAdminService;
use App\Support\ApiHomeCache;

class EventManagementController extends Controller
{
    public function __construct(
        private readonly EventAdminService $eventAdminService,
        private readonly ActivityLogService $activityLogService
    )
    {
    }

    public function index()
    {
        $events = Event::with(['city', 'category', 'tickets'])->latest()->paginate(12);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.form', $this->formData(new Event()));
    }

    public function edit(Event $event)
    {
        $event->loadMissing(['images', 'tickets']);

        return view('admin.events.form', $this->formData($event));
    }

    public function destroy(Event $event)
    {
        $title = $event->title;
        $event->delete();
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'event.deleted',
            $event,
            "تم حذف الفعالية {$title}",
            ['event_title' => $title]
        );

        return redirect()->route('admin.events.index')->with('success', 'تم حذف الفعالية.');
    }

    private function formData(Event $event): array
    {
        return [
            'event' => $event,
            'cities' => City::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => ['draft', 'published', 'scheduled', 'sold_out', 'ended', 'cancelled'],
            'ticketTypes' => ['regular', 'vip', 'vvip', 'early_bird', 'group', 'child', 'adult', 'ladies'],
        ];
    }

    public function store(EventUpsertRequest $request)
    {
        $event = $this->eventAdminService->store(
            $request->eventData(),
            $request->galleryImages(),
            $request->ticketPayloads(),
            auth()->id(),
            $request->file('banner_image'),
            $request->file('gallery_files', [])
        );
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'event.created',
            $event,
            "تم إنشاء الفعالية {$event->title}",
            ['status' => $event->status]
        );

        return redirect()->route('admin.events.edit', $event)->with('success', 'تم إنشاء الفعالية وأنواع التذاكر بنجاح.');
    }

    public function update(EventUpsertRequest $request, Event $event)
    {
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
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'event.updated',
            $event,
            "تم تحديث الفعالية {$event->title}",
            ['status' => $event->fresh()->status]
        );

        return back()->with('success', 'تم تحديث بيانات الفعالية والتذاكر.');
    }
}
