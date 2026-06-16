<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqUpsertRequest;
use App\Models\Faq;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\Request;

class FaqManagementController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $faqs = Faq::query()
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Faq::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.faqs.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => new Faq()]);
    }

    public function store(FaqUpsertRequest $request)
    {
        $faq = Faq::create($request->payload());

        $this->activityLogService->log(
            auth()->id(),
            'faq.created',
            $faq,
            "تم إنشاء سؤال شائع جديد",
            ['category' => $faq->category]
        );

        return redirect()->route('admin.faqs.edit', $faq)->with('success', 'تم إنشاء السؤال بنجاح.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(FaqUpsertRequest $request, Faq $faq)
    {
        $faq->update($request->payload());

        $this->activityLogService->log(
            auth()->id(),
            'faq.updated',
            $faq,
            "تم تحديث سؤال شائع",
            ['category' => $faq->category]
        );

        return back()->with('success', 'تم تحديث السؤال بنجاح.');
    }
}
