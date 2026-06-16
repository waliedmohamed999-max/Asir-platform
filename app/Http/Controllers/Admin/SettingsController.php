<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralSettingsUpdateRequest;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\MediaUploadService;
use App\Services\Admin\SettingsService;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly MediaUploadService $mediaUploadService
    )
    {
    }

    public function edit()
    {
        $defaults = $this->settingsService->defaults();
        $settings = $this->settingsService->getMany($defaults);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(GeneralSettingsUpdateRequest $request)
    {
        $payload = $request->payload();
        $current = $this->settingsService->publicSettings();

        if ($request->boolean('remove_platform_logo')) {
            $this->mediaUploadService->deleteIfManaged($current['platform_logo_url'] ?? null);
            $payload['platform_logo_url'] = null;
        }

        if ($request->hasFile('platform_logo_file')) {
            $this->mediaUploadService->deleteIfManaged($current['platform_logo_url'] ?? null);
            $payload['platform_logo_url'] = $this->mediaUploadService->storeImage($request->file('platform_logo_file'), 'branding');
        }

        if ($request->boolean('remove_platform_favicon')) {
            $this->mediaUploadService->deleteIfManaged($current['platform_favicon_url'] ?? null);
            $payload['platform_favicon_url'] = null;
        }

        if ($request->hasFile('platform_favicon_file')) {
            $this->mediaUploadService->deleteIfManaged($current['platform_favicon_url'] ?? null);
            $payload['platform_favicon_url'] = $this->mediaUploadService->storeImage($request->file('platform_favicon_file'), 'branding');
        }

        $this->settingsService->saveGroup('general', $payload);

        app(ActivityLogService::class)->log(
            auth()->id(),
            'settings.updated',
            null,
            'تم تحديث الإعدادات العامة للمنصة',
            ['platform_name' => $payload['platform_name'] ?? null]
        );

        return back()->with('success', 'تم حفظ إعدادات المنصة بنجاح.');
    }
}
