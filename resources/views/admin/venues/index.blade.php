@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المواقع والقاعات';
    $pageDescription = 'أماكن الفعاليات المرتبطة بالمدن مع السعة والوصف ورابط الخرائط.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">مواقع الفعاليات</span>
            <h2 class="admin-page-title">قائمة المواقع</h2>
            <p class="admin-page-description">أنشئ قاعات وشواطئ وأماكن جاهزة لاستخدامها داخل المنصة لاحقاً بشكل منظم.</p>
        </div>
        <a href="{{ route('admin.venues.create') }}" class="admin-primary-btn">إضافة موقع</a>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($venues as $venue)
            <div class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="h-24 w-28 overflow-hidden rounded-[1.5rem] bg-slate-100">
                            @if($venue->image_url)
                                <img src="{{ $venue->image_url }}" alt="{{ $venue->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-400">بدون صورة</div>
                            @endif
                        </div>
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $venue->city?->name }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $venue->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $venue->is_active ? 'نشط' : 'متوقف' }}</span>
                            </div>
                            <h3 class="mt-3 text-xl font-black">{{ $venue->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $venue->address ?: 'بدون عنوان' }}</p>
                            <p class="mt-2 text-sm text-slate-600">السعة: {{ $venue->capacity ?: 'غير محددة' }} • الترتيب: {{ $venue->sort_order }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.venues.edit', $venue) }}" class="admin-secondary-btn">تعديل</a>
                </div>
            </div>
        @empty
            <div class="admin-card p-8 text-center text-slate-500 xl:col-span-2">لا توجد مواقع بعد.</div>
        @endforelse
    </div>

    <div>{{ $venues->links() }}</div>
</section>
@endsection
