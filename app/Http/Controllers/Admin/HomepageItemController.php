<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageItemUpsertRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Models\HomepageItem;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\MediaUploadService;
use App\Support\ApiHomeCache;

class HomepageItemController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly MediaUploadService $mediaUploadService
    )
    {
    }

    public function index()
    {
        $section = request('section');
        $items = HomepageItem::with(['category', 'city', 'event'])
            ->when($section, fn ($query) => $query->where('section_key', $section))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.homepage-items.index', [
            'items' => $items,
            'section' => $section,
        ]);
    }

    public function appStories()
    {
        $items = HomepageItem::with(['category', 'city', 'event'])
            ->where('section_key', 'app_stories')
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20);

        return view('admin.homepage-items.index', [
            'items' => $items,
            'section' => 'app_stories',
        ]);
    }

    public function create()
    {
        $homepageItem = new HomepageItem([
            'section_key' => request('section', 'hero_banners'),
            'content_type' => request('content_type', request('section') === 'app_stories' ? 'story' : 'event'),
            'ad_type' => request('section') === 'app_stories' ? 'app_story' : null,
            'is_active' => true,
        ]);

        return view('admin.homepage-items.form', $this->formData($homepageItem));
    }

    public function store(HomepageItemUpsertRequest $request)
    {
        $homepageItem = HomepageItem::create($this->buildPayload($request));
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'homepage_item.created',
            $homepageItem,
            "تم إنشاء عنصر رئيسي {$homepageItem->title}",
            ['section_key' => $homepageItem->section_key]
        );

        return redirect()->route('admin.homepage-items.index')->with('success', 'تمت إضافة عنصر الصفحة الرئيسية.');
    }

    public function edit(HomepageItem $homepageItem)
    {
        return view('admin.homepage-items.form', $this->formData($homepageItem));
    }

    public function update(HomepageItemUpsertRequest $request, HomepageItem $homepageItem)
    {
        $homepageItem->update($this->buildPayload($request, $homepageItem));
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'homepage_item.updated',
            $homepageItem,
            "تم تحديث عنصر الصفحة الرئيسية {$homepageItem->title}",
            ['section_key' => $homepageItem->section_key]
        );

        return back()->with('success', 'تم تحديث عنصر الصفحة الرئيسية.');
    }

    public function destroy(HomepageItem $homepageItem)
    {
        $title = $homepageItem->title;
        $homepageItem->delete();
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'homepage_item.deleted',
            null,
            "تم حذف عنصر الصفحة الرئيسية {$title}"
        );

        return redirect()->route('admin.homepage-items.index')->with('success', 'تم حذف العنصر.');
    }

    private function formData(HomepageItem $homepageItem): array
    {
        return [
            'homepageItem' => $homepageItem,
            'categories' => Category::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
            'events' => Event::orderBy('title')->get(),
            'sections' => [
                'app_stories' => 'استوري التطبيق',
                'hero_banners' => 'بنرات الهيرو',
                'featured_events' => 'أبرز الفعاليات',
                'featured_tourism' => 'العالم السياحية المميزة',
                'categories_showcase' => 'الفئات',
                'artists' => 'مشاهير الفنانين',
                'places' => 'الأماكن',
                'today_events' => 'فعاليات اليوم',
                'nightlife' => 'السهرات الليلية',
                'arabic_guide' => 'دليل الفعاليات العربية',
                'theatre' => 'العروض والمسرحيات',
                'nearby_entertainment' => 'الفعاليات القريبة',
                'city_circles' => 'المدن القريبة',
                'other_tags' => 'الفئات الأخرى',
            ],
            'contentTypes' => [
                'story' => 'استوري التطبيق',
                'concert' => 'حفلات',
                'event' => 'فعاليات',
                'place' => 'أماكن',
                'activity' => 'مغامرات وتجارب',
                'category' => 'تصنيف',
                'artist' => 'فنان',
                'city' => 'مدينة',
                'tag' => 'وسم / تصنيف نصي',
            ],
            'adTypes' => [
                'app_story' => 'App Story',
                'homepage_hero' => 'Homepage Hero',
                'sidebar' => 'Sidebar',
                'event_banner' => 'Event Banner',
                'popup' => 'Popup',
                'category_banner' => 'Category Banner',
                'homepage_card' => 'Homepage Card',
            ],
        ];
    }

    private function buildPayload(HomepageItemUpsertRequest $request, ?HomepageItem $homepageItem = null): array
    {
        $payload = $request->payload();

        if ($request->boolean('remove_image') && $homepageItem?->image_url) {
            $this->mediaUploadService->deleteIfManaged($homepageItem->image_url);
            $payload['image_url'] = null;
        }

        if ($request->hasFile('image_file')) {
            $this->mediaUploadService->deleteIfManaged($homepageItem?->image_url);
            $payload['image_url'] = $this->mediaUploadService->storeImage($request->file('image_file'), 'homepage/items');
        }

        if ($request->boolean('remove_hero_image') && $homepageItem?->hero_image_url) {
            $this->mediaUploadService->deleteIfManaged($homepageItem->hero_image_url);
            $payload['hero_image_url'] = null;
        }

        if ($request->hasFile('hero_image_file')) {
            $this->mediaUploadService->deleteIfManaged($homepageItem?->hero_image_url);
            $payload['hero_image_url'] = $this->mediaUploadService->storeImage($request->file('hero_image_file'), 'homepage/heroes');
        }

        $existingGallery = collect($request->input('existing_gallery', []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        $currentGallery = $homepageItem?->gallery ?? [];

        foreach ($currentGallery as $imageUrl) {
            if (! in_array($imageUrl, $existingGallery, true)) {
                $this->mediaUploadService->deleteIfManaged($imageUrl);
            }
        }

        $payload['gallery'] = array_merge(
            $existingGallery,
            $payload['gallery'] ?? [],
            $this->mediaUploadService->storeMany($request->file('gallery_files', []), 'homepage/gallery')
        );

        return $payload;
    }
}
