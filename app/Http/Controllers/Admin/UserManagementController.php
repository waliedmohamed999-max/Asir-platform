<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpsertRequest;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\MediaUploadService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly MediaUploadService $mediaUploadService
    )
    {
    }

    public function index(Request $request)
    {
        $users = User::withCount(['bookings', 'organizedEvents'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = User::availableRoles();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => User::availableRoles(),
        ]);
    }

    public function store(UserUpsertRequest $request)
    {
        $user = User::create($this->buildPayload($request));

        $this->activityLogService->log(
            auth()->id(),
            'user.created',
            $user,
            "تم إنشاء المستخدم {$user->name}",
            ['role' => $user->role]
        );

        return redirect()->route('admin.users.index')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => User::availableRoles(),
        ]);
    }

    public function update(UserUpsertRequest $request, User $user)
    {
        $user->update($this->buildPayload($request, $user));

        $this->activityLogService->log(
            auth()->id(),
            'user.updated',
            $user,
            "تم تحديث المستخدم {$user->name}",
            ['role' => $user->role, 'is_active' => $user->is_active]
        );

        return back()->with('success', 'تم تحديث بيانات المستخدم.');
    }

    private function buildPayload(UserUpsertRequest $request, ?User $user = null): array
    {
        $payload = $request->payload();

        if ($request->boolean('remove_logo') && $user?->logo_url) {
            $this->mediaUploadService->deleteIfManaged($user->logo_url);
            $payload['logo_url'] = null;
        }

        if ($request->hasFile('logo_file')) {
            $this->mediaUploadService->deleteIfManaged($user?->logo_url);
            $payload['logo_url'] = $this->mediaUploadService->storeImage($request->file('logo_file'), 'users');
        }

        return $payload;
    }
}
