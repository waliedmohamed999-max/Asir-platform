@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المدن';
    $pageDescription = 'المدن الأساسية المستخدمة في الفعاليات، المواقع، والإعلانات الموجهة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">نطاق التشغيل</span>
            <h2 class="admin-page-title">قائمة المدن</h2>
            <p class="admin-page-description">تحكم في ترتيب المدن وتفعيلها داخل المنصة.</p>
        </div>
        <a href="{{ route('admin.cities.create') }}" class="admin-primary-btn">إضافة مدينة</a>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($cities as $city)
            <div class="admin-card interactive-card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $city->slug }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $city->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $city->is_active ? 'نشطة' : 'متوقفة' }}</span>
                        </div>
                        <h3 class="mt-3 text-xl font-black">{{ $city->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">الترتيب: {{ $city->sort_order }}</p>
                    </div>
                    <a href="{{ route('admin.cities.edit', $city) }}" class="admin-secondary-btn">تعديل</a>
                </div>
            </div>
        @empty
            <div class="admin-card p-8 text-center text-slate-500 xl:col-span-2">لا توجد مدن حالياً.</div>
        @endforelse
    </div>

    <div>{{ $cities->links() }}</div>
</section>
@endsection
