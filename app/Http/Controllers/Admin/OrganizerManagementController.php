<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpsertRequest;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\MediaUploadService;
use Illuminate\Http\Request;

class OrganizerManagementController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly MediaUploadService $mediaUploadService
    )
    {
    }

    public function index(Request $request)
    {
        $organizers = User::organizers()
            ->withCount('organizedEvents')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.organizers.index', compact('organizers'));
    }

    public function create()
    {
        $organizer = new User(['role' => 'organizer', 'is_active' => true]);

        return view('admin.organizers.form', compact('organizer'));
    }

    public function store(UserUpsertRequest $request)
    {
        $payload = $this->buildPayload($request);
        $payload['role'] = 'organizer';

        $organizer = User::create($payload);

        $this->activityLogService->log(
            auth()->id(),
            'organizer.created',
            $organizer,
            "تم إنشاء المنظم {$organizer->name}",
            ['is_active' => $organizer->is_active]
        );

        return redirect()->route('admin.organizers.show', $organizer)->with('success', 'تم إنشاء المنظم بنجاح.');
    }

    public function show(User $organizer)
    {
        abort_unless($organizer->role === 'organizer', 404);

        $organizer->load(['organizedEvents.city', 'organizedEvents.category']);

        return view('admin.organizers.show', compact('organizer'));
    }

    public function edit(User $organizer)
    {
        abort_unless($organizer->role === 'organizer', 404);

        return view('admin.organizers.form', compact('organizer'));
    }

    public function update(UserUpsertRequest $request, User $organizer)
    {
        abort_unless($organizer->role === 'organizer', 404);

        $payload = $this->buildPayload($request, $organizer);
        $payload['role'] = 'organizer';

        $organizer->update($payload);

        $this->activityLogService->log(
            auth()->id(),
            'organizer.updated',
            $organizer,
            "تم تحديث المنظم {$organizer->name}",
            ['is_active' => $organizer->is_active]
        );

        return back()->with('success', 'تم تحديث بيانات المنظم.');
    }

    private function buildPayload(UserUpsertRequest $request, ?User $organizer = null): array
    {
        $payload = $request->payload();

        if ($request->boolean('remove_logo') && $organizer?->logo_url) {
            $this->mediaUploadService->deleteIfManaged($organizer->logo_url);
            $payload['logo_url'] = null;
        }

        if ($request->hasFile('logo_file')) {
            $this->mediaUploadService->deleteIfManaged($organizer?->logo_url);
            $payload['logo_url'] = $this->mediaUploadService->storeImage($request->file('logo_file'), 'organizers');
        }

        return $payload;
    }
}
