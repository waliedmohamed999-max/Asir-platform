@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المنظمين';
    $pageDescription = 'اعتماد المنظمين، تحديث بياناتهم، ومتابعة فعاليات كل منظم من لوحة الإدارة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">شركاء التشغيل</span>
            <h2 class="admin-page-title">إدارة المنظمين</h2>
            <p class="admin-page-description">اعتمد المنظمين وتابع بياناتهم وعدد فعالياتهم من شاشة واحدة.</p>
        </div>
        <a href="{{ route('admin.organizers.create') }}" class="admin-primary-btn">إضافة منظم</a>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو البريد أو الجوال" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            <button class="admin-primary-btn">بحث</button>
        </form>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($organizers as $organizer)
            <article class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="h-20 w-20 overflow-hidden rounded-[1.5rem] bg-slate-100">
                            @if($organizer->logo_url)
                                <img src="{{ $organizer->logo_url }}" alt="{{ $organizer->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-400">Logo</div>
                            @endif
                        </div>
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $organizer->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $organizer->is_active ? 'معتمد / نشط' : 'موقوف' }}</span>
                            </div>
                            <h3 class="mt-3 text-xl font-black">{{ $organizer->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $organizer->email }} • {{ $organizer->phone ?: 'بدون جوال' }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($organizer->bio, 120) ?: 'لا توجد نبذة حتى الآن.' }}</p>
                            <p class="mt-2 text-sm text-slate-600">عدد الفعاليات: <span class="font-black text-slate-900">{{ $organizer->organized_events_count }}</span></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.organizers.show', $organizer) }}" class="admin-secondary-btn">التفاصيل</a>
                        <a href="{{ route('admin.organizers.edit', $organizer) }}" class="admin-success-btn !text-violet-700 !bg-violet-50 !border-violet-200">تعديل</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-card p-8 text-center text-slate-500 xl:col-span-2">لا يوجد منظمون حالياً.</div>
        @endforelse
    </div>

    <div>{{ $organizers->links() }}</div>
</section>
@endsection
