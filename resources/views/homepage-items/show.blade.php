@extends('layouts.app')

@section('content')
@php
    $actionUrl = $item->cta_url
        ?: ($quickCheckoutUrl ?: ($resolvedEvent?->slug ? route('events.show', $resolvedEvent) . '#booking-section' : null));
@endphp
<section class="mx-auto max-w-7xl px-4 py-10">
    <div class="grid gap-10 lg:grid-cols-[.95fr_1.05fr]">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-[2rem] bg-white shadow-sm">
                <img src="{{ $item->hero_image_url ?: $item->image_url }}" alt="{{ $item->title }}" class="h-[420px] w-full object-cover">
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <span class="text-sm text-slate-500">{{ $item->date_label ?: 'التاريخ يحدد لاحقاً' }}</span>
                    <span class="text-sm text-slate-500">الأسعار تبدأ من</span>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    @if($actionUrl)
                        <a href="{{ $actionUrl }}" @if($item->open_in_new_tab) target="_blank" rel="noreferrer" @endif class="inline-flex rounded-xl bg-slate-900 px-8 py-4 text-lg font-black text-white transition hover:bg-slate-800">
                            {{ $item->cta_label ?: ($resolvedEvent ? 'اختيار التذاكر' : 'عرض التفاصيل') }}
                        </a>
                    @else
                        <button type="button" disabled class="inline-flex cursor-not-allowed rounded-xl bg-slate-300 px-8 py-4 text-lg font-black text-white">
                            {{ $item->cta_label ?: 'غير متاح حالياً' }}
                        </button>
                    @endif
                    <div class="text-left">
                        <p class="text-4xl font-black">{{ $item->price_label ?: 'SAR 0.00' }}</p>
                    </div>
                </div>
                @if($defaultTicket)
                    <p class="mt-4 text-sm text-emerald-600">سيتم فتح شاشة المراجعة مباشرة مع اختيار التذكرة الافتراضية: <span class="font-black">{{ $defaultTicket->name }}</span>.</p>
                @elseif($resolvedEvent)
                    <p class="mt-4 text-sm text-slate-500">سيتم تحويلك إلى صفحة الفعالية لاختيار التذاكر وإتمام الحجز.</p>
                @else
                    <p class="mt-4 text-sm text-slate-500">إذا لم تُحدد رابطاً يدوياً فسيحاول النظام ربط العنصر تلقائياً بأقرب فعالية مطابقة متاحة للحجز.</p>
                @endif
            </div>

            <div class="space-y-4">
                @if($item->schedule)
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-2xl font-black">مواعيد الزيارة / الحضور</h2>
                        <p class="mt-4 whitespace-pre-line leading-8 text-slate-600">{{ $item->schedule }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-8">
            <div>
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <span>{{ $item->city?->name ?? 'جدة' }}</span>
                    <span>•</span>
                    <span>{{ $item->category?->name ?? 'فعاليات' }}</span>
                    @if($item->venue_name)
                        <span>•</span>
                        <span>{{ $item->venue_name }}</span>
                    @elseif($resolvedEvent?->venue_name)
                        <span>•</span>
                        <span>{{ $resolvedEvent->venue_name }}</span>
                    @endif
                </div>
                <h1 class="mt-4 text-4xl font-black leading-tight">{{ $item->title }}</h1>
                <p class="mt-5 leading-8 text-slate-700">{{ $item->description ?: $item->subtitle }}</p>
            </div>

            @if($item->includes)
                <div>
                    <h2 class="text-3xl font-black">تشمل الفعالية</h2>
                    <div class="mt-4 whitespace-pre-line leading-8 text-slate-700">{{ $item->includes }}</div>
                </div>
            @endif

            @if($item->terms)
                <div class="border-t border-slate-200 pt-8">
                    <h2 class="text-3xl font-black">الشروط والأحكام</h2>
                    <div class="mt-4 whitespace-pre-line leading-8 text-slate-700">{{ $item->terms }}</div>
                </div>
            @endif

            @if($item->directions)
                <div class="border-t border-slate-200 pt-8">
                    <h2 class="text-3xl font-black">كيف تصل إلى وجهتك؟</h2>
                    <div class="mt-4 whitespace-pre-line leading-8 text-slate-700">{{ $item->directions }}</div>
                </div>
            @endif

            @if($item->location_title || $item->map_url)
                <div class="border-t border-slate-200 pt-8">
                    <h2 class="text-3xl font-black">الموقع</h2>
                    <div class="mt-4 rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-black">{{ $item->location_title ?: ($item->venue_name ?: 'الموقع') }}</p>
                                <p class="mt-2 text-slate-500">{{ $item->location_code }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm">موقع الفعالية</div>
                        </div>
                    </div>
                    @if($item->map_url)
                        <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <iframe src="{{ $item->map_url }}" width="100%" height="300" style="border:0;" loading="lazy"></iframe>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if($relatedItems->isNotEmpty())
        <div class="mt-16 border-t border-slate-200 pt-10">
            <h2 class="text-3xl font-black">قد يعجبك أيضاً</h2>
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5">
                @foreach($relatedItems as $related)
                    <a href="{{ route('homepage-items.show', $related) }}" class="block rounded-[1.25rem] bg-white p-2 shadow-sm sm:rounded-[1.5rem] sm:p-3">
                        <img src="{{ $related->image_url }}" alt="{{ $related->title }}" class="h-28 w-full rounded-[1rem] object-cover sm:h-40 sm:rounded-[1.25rem]">
                        <h3 class="mt-2 text-[13px] font-black leading-5 sm:mt-3 sm:text-lg sm:leading-7">{{ $related->title }}</h3>
                        <div class="mt-1 flex flex-wrap gap-x-2 gap-y-1 text-[11px] sm:mt-2 sm:text-sm">
                            <span>{{ $related->price_label }}</span>
                            <span class="text-emerald-500">{{ $related->meta_label }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-16 border-t border-slate-200 pt-10">
        <h2 class="text-3xl font-black">ما الذي يميز منصة عسير؟</h2>
        <div class="mt-8 grid gap-6 md:grid-cols-4">
            <div class="text-center"><p class="text-3xl">🛡</p><p class="mt-3 font-black">عملية شراء آمنة</p><p class="mt-2 text-sm text-slate-500">دفع سريع وآمن</p></div>
            <div class="text-center"><p class="text-3xl">💳</p><p class="mt-3 font-black">تأكيد فوري</p><p class="mt-2 text-sm text-slate-500">خدمة ضمان اختيارية</p></div>
            <div class="text-center"><p class="text-3xl">🎫</p><p class="mt-3 font-black">الموقع الرسمي لبيع التذاكر</p><p class="mt-2 text-sm text-slate-500">أكثر من 10 مليون مستخدم</p></div>
            <div class="text-center"><p class="text-3xl">👥</p><p class="mt-3 font-black">خدمة العملاء على مدار الساعة</p><p class="mt-2 text-sm text-slate-500">دعم متخصص</p></div>
        </div>
    </div>
</section>
@endsection
