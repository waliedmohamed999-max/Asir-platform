@extends('layouts.admin')

@php
    $pageTitle = 'تفاصيل المنظم';
    $pageDescription = 'ملف تعريفي كامل للمنظم مع الفعاليات المرتبطة به.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[.9fr_1.1fr]">
        <div class="admin-card admin-page-head">
            <div class="flex items-start gap-4">
                <div class="h-24 w-24 overflow-hidden rounded-[1.5rem] bg-slate-100">
                    @if($organizer->logo_url)
                        <img src="{{ $organizer->logo_url }}" alt="{{ $organizer->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-400">Logo</div>
                    @endif
                </div>
                <div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $organizer->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $organizer->is_active ? 'نشط' : 'موقوف' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Organizer</span>
                    </div>
                    <h2 class="mt-3 text-3xl font-black">{{ $organizer->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $organizer->email }} • {{ $organizer->phone ?: 'بدون جوال' }}</p>
                </div>
            </div>

            <div class="mt-6 space-y-3 text-sm text-slate-600">
                <p><span class="font-black text-slate-900">الواتساب:</span> {{ $organizer->whatsapp ?: '—' }}</p>
                <p><span class="font-black text-slate-900">الموقع:</span> {{ $organizer->website_url ?: '—' }}</p>
                <p><span class="font-black text-slate-900">إنستغرام:</span> {{ $organizer->instagram_url ?: '—' }}</p>
                <p><span class="font-black text-slate-900">X:</span> {{ $organizer->x_url ?: '—' }}</p>
            </div>

            <div class="mt-6 rounded-[1.5rem] bg-slate-50 p-4 text-sm leading-7 text-slate-700">
                {{ $organizer->bio ?: 'لا توجد نبذة تعريفية للمنظم بعد.' }}
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('admin.organizers.edit', $organizer) }}" class="admin-primary-btn">تعديل البيانات</a>
                <a href="{{ route('admin.organizers.index') }}" class="admin-secondary-btn">رجوع</a>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h3 class="text-2xl font-black">فعاليات المنظم</h3>
            <div class="mt-6 space-y-4">
                @forelse($organizer->organizedEvents as $event)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="font-bold">{{ $event->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $event->city?->name }} • {{ $event->category?->name }} • {{ $event->start_date?->translatedFormat('d M Y') }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $event->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-4 py-6 text-center text-slate-500">لا توجد فعاليات مرتبطة بهذا المنظم حتى الآن.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
