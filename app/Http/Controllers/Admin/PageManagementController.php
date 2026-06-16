<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageUpsertRequest;
use App\Models\Page;
use App\Services\Admin\ActivityLogService;

class PageManagementController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index()
    {
        $query = Page::query();

        if (request('scope') === 'footer') {
            $query->where('show_in_footer', true)->orderBy('footer_group')->orderBy('sort_order');
        } else {
            $query->orderBy('sort_order')->latest();
        }

        $pages = $query->paginate(20)->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page()]);
    }

    public function store(PageUpsertRequest $request)
    {
        $page = Page::create($request->payload());

        $this->activityLogService->log(
            auth()->id(),
            'page.created',
            $page,
            "تم إنشاء الصفحة {$page->title}",
            ['slug' => $page->slug]
        );

        return redirect()->route('admin.pages.edit', $page)->with('success', 'تم إنشاء الصفحة بنجاح.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(PageUpsertRequest $request, Page $page)
    {
        $page->update($request->payload());

        $this->activityLogService->log(
            auth()->id(),
            'page.updated',
            $page,
            "تم تحديث الصفحة {$page->title}",
            ['slug' => $page->slug]
        );

        return back()->with('success', 'تم تحديث الصفحة بنجاح.');
    }

    public function destroy(Page $page)
    {
        $title = $page->title;

        $this->activityLogService->log(
            auth()->id(),
            'page.deleted',
            $page,
            "تم حذف الصفحة {$title}",
            ['slug' => $page->slug]
        );

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة بنجاح.');
    }
}
