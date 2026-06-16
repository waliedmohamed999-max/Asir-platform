<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryUpsertRequest;
use App\Models\Category;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\MediaUploadService;
use App\Support\ApiHomeCache;

class CategoryManagementController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly MediaUploadService $mediaUploadService
    )
    {
    }

    public function index()
    {
        $categories = Category::with('parent')->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category(),
            'parents' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(CategoryUpsertRequest $request)
    {
        $category = Category::create($this->buildPayload($request));
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'category.created',
            $category,
            "تم إنشاء التصنيف {$category->name}",
            ['slug' => $category->slug]
        );

        return redirect()->route('admin.categories.index')->with('success', 'تم إنشاء التصنيف بنجاح.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::whereKeyNot($category->id)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(CategoryUpsertRequest $request, Category $category)
    {
        $category->update($this->buildPayload($request, $category));
        ApiHomeCache::clear();

        $this->activityLogService->log(
            auth()->id(),
            'category.updated',
            $category,
            "تم تحديث التصنيف {$category->name}",
            ['slug' => $category->slug]
        );

        return back()->with('success', 'تم تحديث بيانات التصنيف.');
    }

    private function buildPayload(CategoryUpsertRequest $request, ?Category $category = null): array
    {
        $payload = $request->payload();

        if ($request->boolean('remove_image') && $category?->image_url) {
            $this->mediaUploadService->deleteIfManaged($category->image_url);
            $payload['image_url'] = null;
        }

        if ($request->hasFile('image_file')) {
            $this->mediaUploadService->deleteIfManaged($category?->image_url);
            $payload['image_url'] = $this->mediaUploadService->storeImage($request->file('image_file'), 'categories');
        }

        return $payload;
    }
}
