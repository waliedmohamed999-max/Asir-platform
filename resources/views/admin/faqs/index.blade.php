@extends('layouts.admin')

@php
    $pageTitle = 'الأسئلة الشائعة';
    $pageDescription = 'إدارة أسئلة وأجوبة المنصة مع تصنيف داخلي وترتيب الظهور.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">مساعدة العملاء</span>
            <h2 class="admin-page-title">الأسئلة الشائعة</h2>
            <p class="admin-page-description">إدارة أسئلة وأجوبة المنصة مع تصنيف داخلي وترتيب الظهور.</p>
        </div>
        <a href="{{ route('admin.faqs.create') }}" class="admin-primary-btn">إضافة سؤال</a>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="flex gap-3">
            <select name="category" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <button class="admin-primary-btn">فلترة</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($faqs as $faq)
            <article class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $faq->category ?: 'عام' }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $faq->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $faq->is_active ? 'نشط' : 'مخفي' }}</span>
                        </div>
                        <h3 class="text-xl font-black">{{ $faq->question }}</h3>
                        <p class="text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($faq->answer, 180) }}</p>
                    </div>
                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="admin-secondary-btn">تعديل</a>
                </div>
            </article>
        @empty
            <div class="admin-card p-8 text-center text-slate-500">لا توجد أسئلة شائعة حالياً.</div>
        @endforelse
    </div>

    <div>{{ $faqs->links() }}</div>
</section>
@endsection
