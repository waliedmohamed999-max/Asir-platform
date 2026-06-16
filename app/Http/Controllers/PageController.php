<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;

class PageController extends Controller
{
    public function show(Page $page)
    {
        abort_unless($page->is_active, 404);

        return view('pages.show', compact('page'));
    }

    public function faq()
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($faq) => $faq->category ?: 'عام');

        return view('pages.faq', compact('faqs'));
    }
}
