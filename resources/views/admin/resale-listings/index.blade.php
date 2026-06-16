@extends('layouts.admin')

@php
    $pageTitle = 'إدارة إعادة البيع';
    $pageDescription = 'متابعة قوائم التذاكر المعروضة من العملاء، مراجعة حالتها، وإيقاف أو تفعيل القوائم.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">بوابة إعادة البيع</span>
                <h2 class="admin-page-title">سوق التذاكر الثانوية</h2>
                <p class="admin-page-description">كل قائمة هنا مرتبطة بتذكرة مدفوعة داخل المحفظة وتظهر في التطبيق والواجهة الرسمية.</p>
            </div>
            <a href="{{ route('resale.index') }}" target="_blank" class="admin-secondary-btn">عرض السوق</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">كل القوائم</p><p class="mt-3 text-4xl font-black">{{ $stats['total'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">نشطة</p><p class="mt-3 text-4xl font-black text-emerald-700">{{ $stats['active'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">مباعة</p><p class="mt-3 text-4xl font-black text-violet-700">{{ $stats['sold'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">قيمة السوق النشطة</p><p class="mt-3 text-4xl font-black">{{ number_format((float) $stats['value'], 2) }} <span class="text-2xl">ر.س</span></p></div>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input name="search" value="{{ request('search') }}" placeholder="بحث بالمرجع / الفعالية / البائع" class="rounded-2xl border border-slate-200 px-4 py-3 md:col-span-2">
            <select name="status" class="rounded-2xl border border-slate-200 px-4 py-3">
                <option value="">كل الحالات</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <div class="flex gap-3">
                <button class="admin-primary-btn">فلترة</button>
                <a href="{{ route('admin.resale-listings.index') }}" class="admin-secondary-btn">إعادة</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($listings as $listing)
            <article class="admin-card interactive-card p-5">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px] xl:items-center">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <span class="badge-pill badge-pill-muted">{{ $listing->reference }}</span>
                            <span class="badge-pill {{ $listing->status === 'active' ? 'badge-pill-success' : ($listing->status === 'sold' ? 'badge-pill-warning' : 'badge-pill-muted') }}">{{ $listing->status }}</span>
                            <span class="badge-pill badge-pill-muted">{{ $listing->ticket?->name ?? $listing->bookingItem?->ticket_name }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black">{{ $listing->event?->title }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $listing->event?->city?->name }} • البائع: {{ $listing->seller?->name }} • الحجز: {{ $listing->bookingItem?->booking?->reference }}</p>
                        </div>
                        <p class="text-sm text-slate-600">تاريخ العرض: {{ $listing->listed_at?->translatedFormat('d M Y - h:i A') ?? 'غير مسجل' }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-500">سعر إعادة البيع</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format((float) $listing->price, 2) }} ر.س</p>
                        <form method="POST" action="{{ route('admin.resale-listings.update', $listing) }}" class="mt-4 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="min-w-0 flex-1 rounded-2xl border border-slate-200 px-3 py-2 text-sm font-bold">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($listing->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <button class="admin-primary-btn !px-4 !py-2">حفظ</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-empty">لا توجد قوائم إعادة بيع بعد.</div>
        @endforelse
    </div>

    <div>{{ $listings->links() }}</div>
</section>
@endsection
