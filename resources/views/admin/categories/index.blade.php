@extends('layouts.admin')

@php
    $pageTitle = 'إدارة التصنيفات';
    $pageDescription = 'تصنيفات رئيسية وفرعية مع ترتيب وSEO وحالة نشر مناسبة للمنصة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">هيكلة المنصة</span>
            <h2 class="admin-page-title">قائمة التصنيفات</h2>
            <p class="admin-page-description">أنشئ تصنيفات رئيسية وفرعية للتحكم في تنظيم الفعاليات والإعلانات.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="admin-primary-btn">إضافة تصنيف</a>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($categories as $category)
            <div class="admin-card interactive-card p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $category->slug }}</span>
                            @if($category->parent)
                                <span class="badge-pill bg-violet-100 text-violet-700">فرعي من: {{ $category->parent->name }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-black">{{ $category->name }}</h3>
                            <p class="mt-1 text-xs font-bold text-slate-400">AR: {{ $category->name_ar ?: $category->name }} @if($category->name_en) • EN: {{ $category->name_en }} @endif</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $category->description ?: 'بدون وصف' }}</p>
                        </div>
                        <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                            <p>الترتيب: <span class="font-black text-slate-900">{{ $category->sort_order }}</span></p>
                            <p>الأيقونة: <span class="font-black text-slate-900">{{ $category->icon ?: '—' }}</span></p>
                            <p>الحالة: <span class="font-black {{ $category->is_active ? 'text-emerald-600' : 'text-slate-500' }}">{{ $category->is_active ? 'نشط' : 'متوقف' }}</span></p>
                        </div>
                    </div>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="admin-secondary-btn">تعديل</a>
                </div>
            </div>
        @empty
            <div class="admin-empty xl:col-span-2">لا توجد تصنيفات حالياً.</div>
        @endforelse
    </div>

    <div>{{ $categories->links() }}</div>
</section>
@endsection
