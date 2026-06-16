@extends('layouts.admin')

@php
    $pageTitle = 'الصفحات الثابتة';
    $pageDescription = 'إدارة صفحات من نحن، الشروط، الخصوصية، الاسترجاع، والتواصل من لوحة واحدة.';
    $isFooterScope = request('scope') === 'footer';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">المحتوى الثابت</span>
            <h2 class="admin-page-title">{{ $isFooterScope ? 'روابط وصفحات الفوتر' : 'صفحات الموقع' }}</h2>
            <p class="admin-page-description">{{ $isFooterScope ? 'كل ما يظهر في أعمدة الفوتر ويُفتح كصفحات أو روابط مُدارة من الداشبورد.' : 'صفحات ثابتة جاهزة للعرض العام عبر روابط ديناميكية.' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.pages.index') }}" class="{{ $isFooterScope ? 'admin-secondary-btn' : 'admin-primary-btn' }}">كل الصفحات</a>
            <a href="{{ route('admin.pages.index', ['scope' => 'footer']) }}" class="{{ $isFooterScope ? 'admin-primary-btn' : 'admin-secondary-btn' }}">روابط الفوتر</a>
            <a href="{{ route('admin.pages.create') }}" class="admin-primary-btn">إضافة صفحة</a>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($pages as $page)
            <article class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $page->slug }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $page->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $page->is_active ? 'منشورة' : 'مخفية' }}</span>
                            @if($page->show_in_footer)
                                <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">{{ \App\Models\Page::FOOTER_GROUPS[$page->footer_group] ?? 'الفوتر' }}</span>
                            @endif
                        </div>
                        <h3 class="text-xl font-black">{{ $page->title }}</h3>
                        <p class="text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($page->excerpt, 160) ?: 'بدون وصف مختصر' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ $page->publicUrl() }}" @if($page->open_in_new_tab) target="_blank" rel="noreferrer" @else target="_blank" @endif class="admin-secondary-btn">عرض</a>
                        <a href="{{ route('admin.pages.edit', $page) }}" class="admin-success-btn !text-violet-700 !bg-violet-50 !border-violet-200">تعديل</a>
                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('هل تريد حذف هذه الصفحة؟')">
                            @csrf
                            @method('DELETE')
                            <button class="admin-danger-btn !bg-rose-50 !text-rose-700 !border !border-rose-200 !shadow-none">حذف</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-card p-8 text-center text-slate-500">لا توجد صفحات ثابتة حالياً.</div>
        @endforelse
    </div>

    <div>{{ $pages->links() }}</div>
</section>
@endsection
