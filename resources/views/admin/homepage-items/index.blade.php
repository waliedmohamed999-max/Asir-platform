@extends('layouts.admin')

@php
    $isStories = ($section ?? null) === 'app_stories';
    $pageTitle = $isStories ? 'إدارة استوري التطبيق' : 'إدارة الإعلانات والبنرات';
    $pageDescription = $isStories
        ? 'تحكم في دوائر الاستوري الظاهرة أعلى تطبيق الموبايل: الصورة، الترتيب، الربط بفعالية، وحالة النشر.'
        : 'مسار موحد للتحكم في البنرات الإعلانية، الكروت الرئيسية، والاستهداف حسب المدينة أو التصنيف أو الفعالية.';
@endphp

@section('content')
@if($isStories)
    @include('admin.app-stories.index')
@else
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="admin-page-kicker">المحتوى الديناميكي</span>
                <h2 class="admin-page-title">{{ $isStories ? 'استوري التطبيق' : 'إعلانات ومحتوى الصفحة الرئيسية' }}</h2>
                <p class="admin-page-description">{{ $isStories ? 'هذه العناصر تظهر كدوائر استوري داخل التطبيق ويمكن ترتيبها وربطها بفعاليات أو تصنيفات.' : 'هذا المسار يدير البنرات الرئيسية، بطاقات الأقسام، وأي إعلان موجه داخل المنصة.' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($isStories)
                    <a href="{{ route('admin.homepage-items.index') }}" class="admin-secondary-btn">كل عناصر المحتوى</a>
                @else
                    <a href="{{ route('admin.app-stories.index') }}" class="admin-secondary-btn">استوري التطبيق</a>
                @endif
                <a href="{{ route('admin.homepage-items.create', $isStories ? ['section' => 'app_stories', 'content_type' => 'story'] : []) }}" class="admin-primary-btn">{{ $isStories ? 'إضافة استوري جديد' : 'إضافة إعلان / عنصر جديد' }}</a>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($items as $item)
            <article class="admin-card interactive-card overflow-hidden p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        @if($isStories)
                            <div class="h-24 w-24 shrink-0 rounded-full border-4 border-pink-500 bg-slate-900 p-1">
                                <img src="{{ $item->image_url ?: $item->hero_image_url ?: asset('branding/aseer-logo.png') }}" alt="{{ $item->title }}" class="h-full w-full rounded-full object-cover">
                            </div>
                        @else
                            <img src="{{ $item->image_url ?: $item->hero_image_url ?: asset('branding/aseer-logo.png') }}" alt="{{ $item->title }}" class="h-24 w-32 rounded-[1.5rem] object-cover">
                        @endif
                        <div class="space-y-3">
                            <div class="flex flex-wrap gap-2">
                                <span class="badge-pill badge-pill-muted">{{ $item->section_key }}</span>
                                @if($item->ad_type)
                                    <span class="badge-pill bg-violet-100 text-violet-700">{{ $item->ad_type }}</span>
                                @endif
                                <span class="badge-pill badge-pill-muted">{{ $item->content_type }}</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black">{{ $item->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->category?->name ?? 'بدون تصنيف' }} • {{ $item->city?->name ?? 'بدون مدينة' }} • {{ $item->event?->title ?? 'بدون فعالية مرتبطة' }}</p>
                            </div>
                            <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                                <p>الترتيب: <span class="font-black text-slate-900">{{ $item->sort_order }}</span></p>
                                <p>بداية العرض: <span class="font-black text-slate-900">{{ $item->starts_at?->translatedFormat('d M Y') ?? 'فوري' }}</span></p>
                                <p>نهاية العرض: <span class="font-black text-slate-900">{{ $item->ends_at?->translatedFormat('d M Y') ?? 'مفتوح' }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <span class="badge-pill {{ $item->is_active ? 'badge-pill-success' : 'badge-pill-muted' }}">{{ $item->is_active ? 'نشط' : 'متوقف' }}</span>
                        <a href="{{ route('admin.homepage-items.edit', $item) }}" class="admin-secondary-btn">تعديل</a>
                        <form method="POST" action="{{ route('admin.homepage-items.destroy', $item) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟');">
                            @csrf
                            @method('DELETE')
                            <button class="admin-danger-btn">حذف</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-empty xl:col-span-2">لا توجد عناصر بعد. يمكنك إضافة أول إعلان من هذا المسار.</div>
        @endforelse
    </div>

    <div>{{ $items->links() }}</div>
</section>
@endif
@endsection
