<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VenueUpsertRequest;
use App\Models\City;
use App\Models\Venue;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\MediaUploadService;

class VenueManagementController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly MediaUploadService $mediaUploadService
    )
    {
    }

    public function index()
    {
        $venues = Venue::with('city')->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venues.form', [
            'venue' => new Venue(),
            'cities' => City::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(VenueUpsertRequest $request)
    {
        $venue = Venue::create($this->buildPayload($request));

        $this->activityLogService->log(
            auth()->id(),
            'venue.created',
            $venue,
            "تم إنشاء الموقع {$venue->name}",
            ['city_id' => $venue->city_id]
        );

        return redirect()->route('admin.venues.index')->with('success', 'تم إنشاء الموقع بنجاح.');
    }

    public function edit(Venue $venue)
    {
        return view('admin.venues.form', [
            'venue' => $venue,
            'cities' => City::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(VenueUpsertRequest $request, Venue $venue)
    {
        $venue->update($this->buildPayload($request, $venue));

        $this->activityLogService->log(
            auth()->id(),
            'venue.updated',
            $venue,
            "تم تحديث الموقع {$venue->name}",
            ['city_id' => $venue->city_id]
        );

        return back()->with('success', 'تم تحديث بيانات الموقع.');
    }

    private function buildPayload(VenueUpsertRequest $request, ?Venue $venue = null): array
    {
        $payload = $request->payload();

        if ($request->boolean('remove_image') && $venue?->image_url) {
            $this->mediaUploadService->deleteIfManaged($venue->image_url);
            $payload['image_url'] = null;
        }

        if ($request->hasFile('image_file')) {
            $this->mediaUploadService->deleteIfManaged($venue?->image_url);
            $payload['image_url'] = $this->mediaUploadService->storeImage($request->file('image_file'), 'venues');
        }

        return $payload;
    }
}
