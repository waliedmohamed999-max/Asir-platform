@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الفعاليات';
    $pageDescription = 'متابعة الفعاليات، حالتها، ظهورها في الرئيسية، وأنواع التذاكر من شاشة إدارية واحدة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="admin-page-kicker">إدارة المحتوى</span>
                <h2 class="admin-page-title">قائمة الفعاليات</h2>
                <p class="admin-page-description">اعرض وحرر وانقل الفعاليات من المسودة إلى النشر بسهولة.</p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="admin-primary-btn">إضافة فعالية جديدة</a>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($events as $event)
            <article class="admin-card interactive-card overflow-hidden p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="h-24 w-28 overflow-hidden rounded-[1.5rem] bg-slate-100">
                            @if($event->primary_image_url)
                                <img src="{{ $event->primary_image_url }}" alt="{{ $event->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-400">بدون صورة</div>
                            @endif
                        </div>
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $event->city?->name ?? 'بدون مدينة' }}</span>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $event->category?->name ?? 'بدون تصنيف' }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($event->status === 'scheduled' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">{{ $event->status }}</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900">{{ $event->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $event->venue_name }} • {{ $event->start_date?->translatedFormat('d M Y - h:i A') }}</p>
                            </div>
                            <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                                <p>التذاكر: <span class="font-black text-slate-900">{{ $event->tickets->count() }}</span></p>
                                <p>الظهور: <span class="font-black text-slate-900">{{ $event->display_order ?? 0 }}</span></p>
                                <p>الرئيسية: <span class="font-black {{ $event->show_on_homepage ? 'text-emerald-600' : 'text-slate-500' }}">{{ $event->show_on_homepage ? 'نعم' : 'لا' }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($event->is_featured)
                            <span class="rounded-full bg-fuchsia-100 px-3 py-1 text-xs font-bold text-fuchsia-700">مميزة</span>
                        @endif
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $event->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $event->is_active ? 'مفعلة' : 'معطلة' }}
                        </span>
                    </div>
                </div>

                <p class="mt-5 text-sm leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit($event->excerpt ?: strip_tags($event->description), 180) }}</p>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <div class="text-sm text-slate-500">
                        آخر تحديث: {{ $event->updated_at?->diffForHumans() }}
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('events.show', $event) }}" target="_blank" class="admin-secondary-btn">عرض</a>
                        <a href="{{ route('admin.events.edit', $event) }}" class="admin-success-btn !text-violet-700 !bg-violet-50 !border-violet-200">تعديل</a>
                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('هل أنت متأكد من حذف الفعالية؟');">
                            @csrf
                            @method('DELETE')
                            <button class="admin-danger-btn">حذف</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-card p-8 text-center text-slate-500 xl:col-span-2">لا توجد فعاليات بعد. ابدأ بإضافة أول فعالية من لوحة الإدارة.</div>
        @endforelse
    </div>

    <div>{{ $events->links() }}</div>
</section>
@endsection
