@extends('layouts.app')

@section('content')
<section class="bg-slate-950 py-16 text-white">
    <div class="mx-auto max-w-7xl px-4">
        <div class="grid gap-8 lg:grid-cols-[1fr_380px] lg:items-end">
            <div>
                <p class="text-sm font-black text-fuchsia-300">بوابة إعادة البيع</p>
                <h1 class="mt-3 max-w-3xl text-4xl font-black leading-tight md:text-6xl">تذاكر موثقة من محافظ عملاء منصة عسير</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">كل تذكرة معروضة هنا مرتبطة بحجز مدفوع وتظهر بنفس البيانات داخل التطبيق ولوحة التحكم.</p>
            </div>
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-300">القوائم النشطة</p>
                <p class="mt-2 text-5xl font-black">{{ $listings->total() }}</p>
                <a href="{{ route('bookings.index') }}" class="mt-5 inline-flex rounded-full bg-fuchsia-600 px-5 py-3 text-sm font-black text-white">اعرض تذاكرك للبيع</a>
            </div>
        </div>
    </div>
</section>

<section class="bg-slate-50 py-12">
    <div class="mx-auto max-w-7xl px-4">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($listings as $listing)
                @php
                    $image = $listing->event?->primary_image_url;
                @endphp
                <article class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="relative h-56">
                        @if($image)
                            <img src="{{ $image }}" alt="{{ $listing->event?->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-slate-900 text-white">منصة عسير</div>
                        @endif
                        <span class="absolute right-4 top-4 rounded-full bg-emerald-500 px-3 py-1 text-xs font-black text-white">موثقة</span>
                    </div>
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700">{{ $listing->ticket?->name ?? $listing->bookingItem?->ticket_name }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $listing->reference }}</span>
                        </div>
                        <h2 class="mt-4 text-xl font-black text-slate-950">{{ $listing->event?->title }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ $listing->event?->city?->name }} • {{ $listing->listed_at?->translatedFormat('d M Y') }}</p>
                        <div class="mt-5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold text-slate-400">سعر إعادة البيع</p>
                                <p class="text-2xl font-black text-slate-950">{{ number_format((float) $listing->price, 2) }} ر.س</p>
                            </div>
                            <a href="{{ route('events.show', $listing->event) }}" class="rounded-full bg-slate-950 px-4 py-2 text-sm font-black text-white">تفاصيل الفعالية</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">لا توجد تذاكر معروضة لإعادة البيع حالياً.</div>
            @endforelse
        </div>

        <div class="mt-8">{{ $listings->links() }}</div>
    </div>
</section>
@endsection
