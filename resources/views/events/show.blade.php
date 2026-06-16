@extends('layouts.app')

@section('content')
@php
    $heroImage = $event->images->first()?->image_url ?: $event->primary_image_url ?: asset('branding/aseer-logo.png');
    $galleryImages = $event->images->take(6);
@endphp

<section class="mx-auto max-w-7xl px-4 py-6 sm:py-10">
    <div class="mb-5 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500 sm:text-sm">
        <a href="{{ route('home') }}" class="rounded-full bg-white px-4 py-2 shadow-sm transition hover:text-violet-700">الرئيسية</a>
        <span>/</span>
        <span>{{ $event->category?->name ?? 'فعاليات' }}</span>
        <span>/</span>
        <span class="text-slate-900">{{ $event->title }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px] xl:items-start">
        <div class="space-y-6">
            <article class="overflow-hidden rounded-[1.5rem] bg-white shadow-[0_18px_55px_rgba(15,23,42,.08)] sm:rounded-[2rem]">
                <div class="relative">
                    <img src="{{ $heroImage }}" alt="{{ $event->title }}" class="h-[320px] w-full object-cover sm:h-[460px]">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/15 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-8">
                        <div class="mb-3 flex flex-wrap items-center gap-2 text-xs font-black">
                            <span class="rounded-full bg-white/15 px-3 py-1.5 backdrop-blur">{{ $event->city?->name ?? 'جدة' }}</span>
                            <span class="rounded-full bg-white/15 px-3 py-1.5 backdrop-blur">{{ $event->category?->name ?? 'فعاليات' }}</span>
                            @if($event->is_featured)
                                <span class="rounded-full bg-violet-600 px-3 py-1.5">مميز</span>
                            @endif
                        </div>
                        <h1 class="max-w-3xl text-3xl font-black leading-tight sm:text-5xl">{{ $event->title }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">{{ $event->excerpt ?: str($event->description)->limit(150) }}</p>
                    </div>
                </div>
            </article>

            @if($galleryImages->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($galleryImages as $image)
                        <img src="{{ $image->image_url }}" alt="{{ $image->alt_text ?: $event->title }}" class="h-28 w-full rounded-[1rem] object-cover shadow-sm sm:h-36 sm:rounded-[1.25rem]">
                    @endforeach
                </div>
            @endif

            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:rounded-[2rem] sm:p-7">
                <div class="grid gap-4 text-sm sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black text-slate-400">المدينة</p>
                        <p class="mt-1 font-black text-slate-950">{{ $event->city?->name ?? 'جدة' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black text-slate-400">المكان</p>
                        <p class="mt-1 font-black text-slate-950">{{ $event->venue_name ?: 'يحدد لاحقاً' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black text-slate-400">السعر يبدأ من</p>
                        <p class="mt-1 font-black text-violet-700">{{ number_format((float) $event->starting_price, 2) }} ر.س</p>
                    </div>
                </div>

                <div class="mt-7">
                    <h2 class="text-2xl font-black text-slate-950">عن الفعالية</h2>
                    <p class="mt-3 whitespace-pre-line leading-8 text-slate-600">{{ $event->description }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-xl font-black text-slate-950">جدول المواعيد</h2>
                    <p class="mt-4 whitespace-pre-line leading-8 text-slate-600">{{ $event->schedule_notes ?: 'سيتم الإعلان عن تفاصيل المواعيد قريباً.' }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-xl font-black text-slate-950">الشروط والأحكام</h2>
                    <p class="mt-4 whitespace-pre-line leading-8 text-slate-600">{{ $event->terms ?: 'تطبق الشروط العامة للمنصة وسياسة الحضور الخاصة بالفعالية.' }}</p>
                </div>
            </div>

            @if($event->map_url)
                <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm sm:rounded-[2rem]">
                    <iframe src="{{ $event->map_url }}" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            @endif
        </div>

        <aside id="booking-section" class="xl:sticky xl:top-28">
            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-3 shadow-[0_18px_55px_rgba(15,23,42,.08)] sm:rounded-[2rem] sm:p-4">
                @livewire('event-booking-widget', ['event' => $event], key('event-booking-'.$event->id))
            </div>
        </aside>
    </div>
</section>
@endsection
