@extends('layouts.app')

@section('content')
@php
    $sectionItems = fn (string $key) => collect($homepageSections->get($key, collect()));
    $fallbackImage = asset('branding/aseer-logo.png');

    $mapCards = fn ($items) => collect($items)->map(function ($item) {
        $resolvedUrl = $item->cta_url
            ?: ($item->event?->slug ? route('events.show', $item->event) : route('homepage-items.show', $item));

        return [
            'title' => $item->title,
            'price' => $item->price_label ?? '',
            'meta' => $item->meta_label ?? '',
            'badge' => $item->badge,
            'score' => $item->rating,
            'image' => $item->hero_image_url ?: $item->image_url ?: asset('branding/aseer-logo.png'),
            'subtitle' => $item->subtitle,
            'button' => $item->cta_label ?? 'عرض التفاصيل',
            'url' => $resolvedUrl,
        ];
    })->values();

    $featuredEventCards = $featuredEvents->map(function ($event) {
        return [
            'title' => $event->title,
            'price' => 'من ' . number_format((float) $event->starting_price, 2) . ' ر.س',
            'meta' => $event->city?->name ?? '',
            'badge' => $event->is_featured ? 'مميز' : null,
            'score' => null,
            'image' => $event->primary_image_url ?: asset('branding/aseer-logo.png'),
            'subtitle' => $event->excerpt ?: str($event->description)->limit(110),
            'button' => 'احجز الآن',
            'url' => route('events.show', $event),
        ];
    });

    $heroBanners = $mapCards($sectionItems('hero_banners'));
    $featuredCards = $mapCards($sectionItems('featured_events'));
    $tourismCards = $mapCards($sectionItems('featured_tourism'));
    $todayCards = $mapCards($sectionItems('today_events'));
    $nightlifeCards = $mapCards($sectionItems('nightlife'));
    $arabicGuideCards = $mapCards($sectionItems('arabic_guide'));
    $theatreCards = $mapCards($sectionItems('theatre'));
    $nearbyEntertainmentCards = $mapCards($sectionItems('nearby_entertainment'));
    $categoriesShowcase = $sectionItems('categories_showcase');
    $artists = $sectionItems('artists');
    $places = $sectionItems('places');
    $cityCircles = $sectionItems('city_circles');
    $otherTags = $sectionItems('other_tags');
    $upcomingEventCards = $events->getCollection()->map(function ($event) {
        return [
            'title' => $event->title,
            'price' => 'من ' . number_format((float) $event->starting_price, 2) . ' ر.س',
            'meta' => $event->start_date?->translatedFormat('d M') . ' • ' . ($event->city?->name ?? 'بدون مدينة'),
            'badge' => $event->is_featured ? 'مميز' : null,
            'score' => null,
            'image' => $event->primary_image_url ?: asset('branding/aseer-logo.png'),
            'subtitle' => $event->excerpt ?: str($event->description)->limit(100),
            'button' => 'احجز الآن',
            'url' => route('events.show', $event),
        ];
    });
    $primaryFeaturedCards = $featuredCards->isNotEmpty() ? $featuredCards : $featuredEventCards;
    $dateOptions = [
        '' => 'كل التواريخ',
        'today' => 'اليوم',
        'tomorrow' => 'غداً',
        'weekend' => 'نهاية الأسبوع',
    ];
    $categoryIcons = [
        'today' => '▣',
        'experiences' => '🎟',
        'sports' => '🏆',
        'football' => '⚽',
        'restaurants' => '🍴',
        'aviation' => '✈',
        'hotels' => '◒',
        'concerts' => '♪',
        'shows' => '☻',
        'store' => '▢',
        'auctions' => '⚒',
        'more' => '▦',
    ];

    $renderCard = function ($card, $compact = false) {
        $titleSize = $compact ? 'text-[13px] sm:text-[16px]' : 'text-[14px] sm:text-[18px]';
        $imageHeight = $compact ? 'h-28 sm:h-36' : 'h-32 sm:h-48';
        $priceSize = $compact ? 'text-[12px] sm:text-[16px]' : 'text-[13px] sm:text-[18px]';
        $badge = $card['badge']
            ? '<div class="home-card-badge absolute right-2 top-2 rounded-full bg-violet-700 px-2 py-0.5 text-[10px] font-bold text-white sm:right-3 sm:top-3 sm:px-3 sm:py-1 sm:text-xs">'.$card['badge'].'</div>'
            : '';

        return <<<HTML
            <article class="home-event-card group">
                <div class="relative overflow-hidden rounded-[16px] bg-slate-100 sm:rounded-[22px]">
                    <img src="{$card['image']}" alt="{$card['title']}" class="home-card-image {$imageHeight} w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                    {$badge}
                    <div class="home-card-fav absolute left-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/35 text-lg text-white backdrop-blur sm:left-3 sm:top-3 sm:h-10 sm:w-10 sm:text-2xl">♡</div>
                </div>
                <a href="{$card['url']}" class="block">
                    <h3 class="mt-2 {$titleSize} font-black leading-tight text-slate-900 sm:mt-3">{$card['title']}</h3>
                    <div class="home-card-meta mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500 sm:mt-1.5 sm:gap-x-3 sm:text-sm">
                        <span class="home-card-price {$priceSize} text-slate-700">{$card['price']}</span>
                        <span class="text-[11px] text-emerald-500 sm:text-[13px]">{$card['meta']}</span>
                    </div>
                </a>
            </article>
        HTML;
    };
@endphp

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .home-hero-shell {
            max-width: 1240px;
            margin-inline: auto;
            padding-inline: 16px;
        }
        .home-hero-card {
            min-height: 430px;
            border-radius: 28px;
            overflow: hidden;
            background: #0f172a;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .14);
        }
        .home-filter-card {
            margin-top: -44px;
            position: relative;
            z-index: 10;
            border: 1px solid #e7ecf4;
            border-radius: 24px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 22px 60px rgba(15, 23, 42, .10);
            backdrop-filter: blur(14px);
        }
        .home-select {
            width: 100%;
            min-height: 48px;
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            background: #fff;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            outline: none;
        }
        .home-select:focus {
            border-color: rgba(109,40,217,.45);
            box-shadow: 0 0 0 4px rgba(109,40,217,.08);
        }
        .home-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        @media (min-width: 640px) {
            .home-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        }
        @media (min-width: 1024px) {
            .home-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
        }
        @media (min-width: 1280px) {
            .home-card-grid.five-up { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .home-card-grid.three-up {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 768px) {
            .home-card-grid.three-up { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .home-event-card {
            height: 100%;
            border-radius: 24px;
            background: #fff;
            border: 1px solid #edf1f7;
            padding: 14px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }
        .home-event-card:hover {
            transform: translateY(-3px);
            border-color: rgba(109, 40, 217, .22);
            box-shadow: 0 22px 52px rgba(15, 23, 42, .10);
        }
        .home-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
        }
        .home-section-head h2 {
            font-size: clamp(28px, 3vw, 42px);
            line-height: 1.18;
            font-weight: 900;
            color: #0f172a;
        }
        .home-dark-band {
            border-radius: 28px;
            background: radial-gradient(circle at 80% 0%, rgba(124,58,237,.28), transparent 34%), linear-gradient(135deg, #0d1020, #17112e 58%, #2b145e);
            box-shadow: 0 24px 60px rgba(15, 23, 42, .14);
        }
        .home-category-tile {
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.045);
            padding: 18px 10px;
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
        }
        .home-category-tile:hover {
            transform: translateY(-2px);
            border-color: rgba(232,53,109,.35);
            background: rgba(255,255,255,.075);
        }
        .home-collection-panel {
            border: 1px solid #e7ecf4;
            border-radius: 32px;
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.96));
            box-shadow: 0 22px 58px rgba(15, 23, 42, .07);
            overflow: hidden;
        }
        .home-circle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
            gap: 18px;
        }
        .home-circle-card {
            display: block;
            border-radius: 24px;
            padding: 14px 10px;
            text-align: center;
            transition: transform .22s ease, background .22s ease, box-shadow .22s ease;
        }
        .home-circle-card:hover {
            transform: translateY(-3px);
            background: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .08);
        }
        .home-circle-card img {
            width: 132px;
            height: 132px;
            max-width: 100%;
            border-radius: 999px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .12);
        }
        .home-place-card {
            display: block;
            overflow: hidden;
            border-radius: 26px;
            background: #fff;
            border: 1px solid #edf1f7;
            box-shadow: 0 16px 42px rgba(15, 23, 42, .07);
            transition: transform .22s ease, box-shadow .22s ease;
        }
        .home-place-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 58px rgba(15, 23, 42, .11);
        }
        .home-city-chip {
            display: flex;
            align-items: center;
            gap: 14px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid #edf1f7;
            padding: 12px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
        }
        .home-city-chip img {
            width: 74px;
            height: 74px;
            border-radius: 999px;
            object-fit: cover;
        }
        .home-horizontal-row {
            display: grid;
            gap: 14px;
        }

        .hero-slide-enter,
        .hero-slide-leave {
            transition: transform .5s ease, opacity .5s ease;
        }

        .hero-slide-enter-start {
            opacity: 0;
            transform: translateX(32px);
        }

        .hero-slide-enter-end {
            opacity: 1;
            transform: translateX(0);
        }

        .hero-slide-leave-start {
            opacity: 1;
            transform: translateX(0);
        }

        .hero-slide-leave-end {
            opacity: 0;
            transform: translateX(-32px);
        }

        @media (max-width: 768px) {
            .home-hero-shell {
                padding-inline: 12px;
                padding-top: 18px;
                padding-bottom: 8px;
            }
            .home-hero-card {
                min-height: 420px;
                border-radius: 20px;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
            }
            .home-filter-card {
                margin-top: 14px;
                border-radius: 22px;
                padding: 14px !important;
            }
            .home-section-head { align-items: flex-start; flex-direction: column; }
            .home-section-head h2 { font-size: 26px; }
            .home-event-card {
                border-radius: 20px;
                padding: 8px;
                box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
            }
            .home-event-card .home-card-image {
                height: 118px !important;
                border-radius: 16px;
            }
            .home-event-card h3 {
                margin-top: 9px !important;
                font-size: 13px !important;
                line-height: 1.45 !important;
                min-height: 38px;
            }
            .home-event-card .home-card-meta {
                gap: 2px 8px;
                font-size: 11px;
            }
            .home-event-card .home-card-price {
                font-size: 12px !important;
            }
            .home-event-card .home-card-fav {
                width: 30px;
                height: 30px;
                font-size: 18px;
            }
            .home-event-card .home-card-badge {
                padding: 3px 8px;
                font-size: 10px;
            }
            .home-dark-band,
            .home-collection-panel {
                border-radius: 22px;
            }
            .home-circle-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }
            .home-circle-card { padding: 10px 6px; }
            .home-circle-card img {
                width: 108px;
                height: 108px;
            }
            .home-place-card { border-radius: 20px; }
            .home-city-chip {
                border-radius: 18px;
                padding: 10px;
                min-width: 190px;
                flex: 0 0 190px;
            }
            .home-city-chip img {
                width: 58px;
                height: 58px;
            }
            .home-horizontal-row {
                display: flex;
                overflow-x: auto;
                gap: 12px;
                padding: 2px 2px 10px;
                margin-inline: -2px;
                scroll-snap-type: x mandatory;
                scrollbar-width: none;
            }
            .home-horizontal-row::-webkit-scrollbar { display: none; }
            .home-horizontal-row > * { scroll-snap-align: start; }
            .home-tag-chip {
                flex: 0 0 auto;
                white-space: nowrap;
            }
        }
    </style>
@endpush

<section class="home-hero-shell py-8" id="new-in-jeddah">
    @if($heroBanners->isNotEmpty())
        <div
            class="relative"
            x-data="{
                active: 0,
                banners: {{ Illuminate\Support\Js::from($heroBanners->all()) }},
                timer: null,
                start() {
                    if (this.banners.length <= 1) return;
                    this.stop();
                    this.timer = setInterval(() => {
                        this.active = (this.active + 1) % this.banners.length;
                    }, 5000);
                },
                stop() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },
                next() {
                    this.active = (this.active + 1) % this.banners.length;
                    this.start();
                },
                prev() {
                    this.active = (this.active - 1 + this.banners.length) % this.banners.length;
                    this.start();
                }
            }"
            x-init="start()"
            @mouseenter="stop()"
            @mouseleave="start()"
        >
            <div class="home-hero-card relative">
                <template x-for="(banner, index) in banners" :key="index">
                    <article
                        x-cloak
                        x-show="active === index"
                        x-transition:enter="hero-slide-enter"
                        x-transition:enter-start="hero-slide-enter-start"
                        x-transition:enter-end="hero-slide-enter-end"
                        x-transition:leave="hero-slide-leave"
                        x-transition:leave-start="hero-slide-leave-start"
                        x-transition:leave-end="hero-slide-leave-end"
                        class="absolute inset-0"
                    >
                        <img :src="banner.image" :alt="banner.title" class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/92 via-black/42 to-black/10 md:bg-gradient-to-l md:from-black/88 md:via-black/48 md:to-black/20"></div>
                        <div class="absolute inset-x-0 bottom-0 flex w-full max-w-[620px] flex-col justify-end px-5 pb-7 pt-20 text-right text-white md:inset-y-0 md:right-0 md:justify-center md:px-12 md:pb-0 md:pt-0 lg:px-14">
                            <div class="mb-3 flex items-center justify-start gap-3 md:mb-5">
                                <img src="{{ asset('branding/aseer-logo.png') }}" alt="شعار منصة عسير" class="h-9 w-auto rounded bg-white/90 p-1 object-contain">
                                <div>
                                    <p class="text-xs font-black text-white/70">جدة</p>
                                    <p class="text-sm font-black" x-text="banner.badge || 'فعالية مميزة'"></p>
                                </div>
                            </div>
                            <p class="w-fit rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-black text-white/80 md:px-4 md:py-2 md:text-xs" x-text="banner.meta || 'متاح للحجز الآن'"></p>
                            <h2 class="mt-3 max-w-xl text-[28px] font-black leading-tight md:mt-4 md:text-[48px] lg:text-[56px]" x-text="banner.title"></h2>
                            <p class="mt-3 max-w-lg text-xs leading-6 text-slate-100 md:mt-5 md:text-[17px] md:leading-8" x-text="banner.subtitle"></p>
                            <div class="mt-5 flex flex-wrap items-center gap-2 md:mt-7 md:gap-3">
                                <a :href="banner.url || '#'" class="inline-flex min-h-[42px] items-center justify-center rounded-full bg-white px-6 text-xs font-black text-slate-950 transition hover:bg-violet-100 md:min-h-[48px] md:px-8 md:text-base" x-text="banner.button"></a>
                                <span class="rounded-full border border-white/20 px-4 py-2 text-xs font-black text-white/80 md:px-5 md:py-3 md:text-sm" x-text="banner.price || 'تذاكر متاحة'"></span>
                            </div>
                        </div>
                    </article>
                </template>
                <button @click="prev()" class="absolute left-4 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-xl text-slate-800 shadow-lg transition hover:scale-105 md:flex">←</button>
                <button @click="next()" class="absolute right-4 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-xl text-slate-800 shadow-lg transition hover:scale-105 md:flex">→</button>
            </div>
            <div class="mt-4 flex items-center justify-center gap-2">
                <template x-for="(banner, index) in banners" :key="'dot-' + index">
                    <button @click="active = index; start()" class="h-2.5 rounded-full transition-all" :class="active === index ? 'w-8 bg-violet-700' : 'w-2.5 bg-slate-300'"></button>
                </template>
            </div>
        </div>
    @endif

    <div class="home-filter-card p-4 md:p-5">
        <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black text-violet-700">فلترة ذكية وتحديث مباشر</p>
                <h1 class="mt-1 text-[24px] font-black leading-tight text-slate-950 md:text-3xl">ابحث عن فعاليتك القادمة</h1>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-2xl bg-slate-50 px-2 py-2 md:px-4">
                    <p class="text-lg font-black text-slate-950">{{ number_format($events->total()) }}</p>
                    <p class="text-[11px] font-bold text-slate-500">فعالية</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-2 py-2 md:px-4">
                    <p class="text-lg font-black text-slate-950">{{ $cities->count() }}</p>
                    <p class="text-[11px] font-bold text-slate-500">مدن</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-2 py-2 md:px-4">
                    <p class="text-lg font-black text-slate-950">{{ $categories->count() }}</p>
                    <p class="text-[11px] font-bold text-slate-500">تصنيف</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('home') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto_auto] lg:items-end">
            <label class="block">
                <span class="mb-2 block text-xs font-black text-slate-500">المدينة</span>
                <select name="city" class="home-select">
                    <option value="">كل المدن</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->slug }}" @selected(request('city') === $city->slug)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-2 block text-xs font-black text-slate-500">التصنيف</span>
                <select name="category" class="home-select">
                    <option value="">كل التصنيفات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-2 block text-xs font-black text-slate-500">التاريخ</span>
                <select name="date" class="home-select">
                    @foreach($dateOptions as $key => $label)
                        <option value="{{ $key }}" @selected($selectedDate === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="min-h-[46px] rounded-2xl bg-slate-950 px-7 text-sm font-black text-white transition hover:bg-violet-700">بحث</button>
            <a href="{{ route('home') }}" class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 px-5 text-sm font-black text-slate-600 transition hover:border-violet-200 hover:text-violet-700">إعادة ضبط</a>
        </form>

        <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
            @foreach($categories->take(8) as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}" class="shrink-0 rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-violet-50 hover:text-violet-700">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>
</section>

<section class="section-shell py-8">
    <div class="home-dark-band p-5 text-white md:p-7">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black text-fuchsia-300">تصنيفات مباشرة من لوحة التحكم</p>
                <h2 class="mt-1 text-2xl font-black md:text-3xl">استكشف حسب اهتمامك</h2>
            </div>
            <a href="#upcoming-events" class="rounded-full bg-white/10 px-4 py-2 text-xs font-black text-white transition hover:bg-white/15">كل الفعاليات</a>
        </div>
        <div class="grid grid-cols-3 gap-7 text-center sm:grid-cols-4 lg:grid-cols-12">
            @foreach($categories->take(12) as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}" class="home-category-tile group">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center text-4xl text-white transition group-hover:text-fuchsia-400">
                        {{ $categoryIcons[$category->slug] ?? '▦' }}
                    </div>
                    <p class="mt-3 text-sm font-black">{{ $category->name_ar ?: $category->name }}</p>
                    @if(in_array($category->slug, ['aviation', 'hotels', 'auctions'], true))
                        <p class="mt-1 text-xs font-black text-fuchsia-400">جديد</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>

@if($primaryFeaturedCards->isNotEmpty())
    <section class="section-shell py-10">
        <div class="home-section-head">
            <div>
                <h2>أبرز الفعاليات</h2>
                <p class="mt-2 text-sm text-slate-500">مجموعة مختارة من الفعاليات الأكثر بروزاً على المنصة الآن.</p>
            </div>
            <a href="#upcoming-events" class="text-lg font-bold text-slate-500">عرض المزيد ‹</a>
        </div>
        <div class="home-card-grid mt-8">
            @foreach($primaryFeaturedCards as $card)
                {!! $renderCard($card, true) !!}
            @endforeach
        </div>
    </section>
@endif

@if($tourismCards->isNotEmpty())
    <section class="section-shell py-10">
        <div class="home-section-head">
            <div>
                <h2>العوالم السياحية المميزة</h2>
                <p class="mt-2 text-sm text-slate-500">أماكن وتجارب مرشحة للظهور البارز في الصفحة الرئيسية.</p>
            </div>
            <a href="#places" class="text-lg font-bold text-slate-500">استكشف الأقسام ‹</a>
        </div>
        <div class="home-card-grid mt-8">
            @foreach($tourismCards as $card)
                <article class="home-event-card">
                    <div class="relative overflow-hidden rounded-[16px] sm:rounded-[22px]">
                        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="home-card-image h-28 w-full object-cover sm:h-40">
                        <div class="home-card-fav absolute left-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/35 text-lg text-white backdrop-blur sm:left-3 sm:top-3 sm:h-10 sm:w-10 sm:text-2xl">♡</div>
                        @if($card['badge'])
                            <div class="home-card-badge absolute right-2 top-2 rounded-full bg-violet-700 px-2 py-0.5 text-[10px] font-bold text-white sm:right-3 sm:top-3 sm:px-3 sm:py-1 sm:text-xs">{{ $card['badge'] }}</div>
                        @endif
                        @if($card['score'])
                            <div class="absolute left-3 top-3 rounded-full bg-lime-500 px-3 py-1 text-sm font-black text-white">{{ $card['score'] }}</div>
                        @endif
                    </div>
                    <a href="{{ $card['url'] }}" class="block">
                        <h3 class="mt-2 text-[13px] font-black leading-tight sm:mt-3 sm:text-[17px]">{{ $card['title'] }}</h3>
                        <div class="home-card-meta mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] sm:mt-1.5 sm:gap-x-3 sm:text-[15px]">
                            <span class="home-card-price text-[12px] sm:text-[15px]">{{ $card['price'] }}</span>
                            @if($card['meta'])
                                <span class="text-emerald-500">{{ $card['meta'] }}</span>
                            @endif
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </section>
@endif

@if($categoriesShowcase->isNotEmpty() || $artists->isNotEmpty())
    <section class="section-shell py-10">
        <div class="home-collection-panel p-5 md:p-8">
            <div class="grid gap-10 lg:grid-cols-[1.1fr_.9fr]">
                @if($categoriesShowcase->isNotEmpty())
                    <div>
                        <div class="home-section-head">
                            <div>
                                <p class="text-xs font-black text-violet-700">اختيارات قابلة للإدارة</p>
                                <h2>الفئات</h2>
                                <p class="mt-2 text-sm text-slate-500">تنقل سريع بين أنواع المحتوى والفعاليات الأكثر طلباً.</p>
                            </div>
                            <a href="#upcoming-events" class="text-sm font-black text-slate-500">اذهب للنتائج ‹</a>
                        </div>
                        <div class="home-circle-grid mt-7">
                            @foreach($categoriesShowcase as $item)
                                <a href="{{ route('homepage-items.show', $item) }}" class="home-circle-card">
                                    <img src="{{ $item->image_url ?: $fallbackImage }}" alt="{{ $item->title }}" class="mx-auto">
                                    <p class="mt-4 text-[15px] font-black leading-6 text-slate-950">{{ $item->title }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($artists->isNotEmpty())
                    <div class="rounded-[28px] bg-slate-950 p-5 text-white md:p-6">
                        <div class="home-section-head">
                            <div>
                                <p class="text-xs font-black text-fuchsia-300">أسماء بارزة</p>
                                <h2 class="!text-white">مشاهير الفنانين</h2>
                                <p class="mt-2 text-sm text-white/55">أبرز الفنانين والعروض المرتبطة بهم.</p>
                            </div>
                        </div>
                        <div class="mt-7 grid grid-cols-2 gap-5">
                            @foreach($artists as $artist)
                                <a href="{{ route('homepage-items.show', $artist) }}" class="text-center">
                                    <img src="{{ $artist->image_url ?: $fallbackImage }}" alt="{{ $artist->title }}" class="mx-auto h-32 w-32 rounded-full border-4 border-white/10 object-cover shadow-2xl">
                                    <p class="mt-3 text-[16px] font-black leading-6">{{ $artist->title }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

@if($places->isNotEmpty())
    <section class="section-shell py-12" id="places">
        <div class="home-section-head">
            <div>
                <p class="text-xs font-black text-violet-700">وجهات ومواقع</p>
                <h2>الأماكن</h2>
                <p class="mt-2 text-sm text-slate-500">وجهات يمكن اكتشافها من الصفحة الرئيسية وربطها بمحتوى تفصيلي وفعاليات مرتبطة.</p>
            </div>
            <a href="#upcoming-events" class="text-lg font-bold text-slate-500">تصفح الفعاليات المرتبطة ‹</a>
        </div>
        <div class="home-card-grid five-up mt-8">
            @foreach($places as $place)
                <a href="{{ route('homepage-items.show', $place) }}" class="home-place-card">
                    <img src="{{ $place->image_url ?: $fallbackImage }}" alt="{{ $place->title }}" class="h-28 w-full object-cover sm:h-44">
                    <div class="p-3 sm:p-4">
                        <h3 class="text-[13px] font-black leading-5 text-slate-950 sm:text-[18px] sm:leading-7">{{ $place->title }}</h3>
                        <p class="mt-1 text-[11px] font-bold text-slate-400 sm:mt-2 sm:text-sm">{{ $place->meta_label ?: 'فعاليات قادمة' }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if($todayCards->isNotEmpty())
    <section class="section-shell py-12">
        <div class="home-section-head">
            <div>
                <h2>فعاليات اليوم</h2>
                <p class="mt-2 text-sm text-slate-500">اختيارات جاهزة لمن يريد حضور فعالية اليوم بسرعة.</p>
            </div>
            <a href="{{ route('home', ['date' => 'today']) }}" class="text-lg font-bold text-slate-500">فلترة اليوم ‹</a>
        </div>
        <div class="home-card-grid mt-8">
            @foreach($todayCards as $card)
                {!! $renderCard($card, true) !!}
            @endforeach
        </div>
    </section>
@endif

@if($nightlifeCards->isNotEmpty() || $arabicGuideCards->isNotEmpty())
    <section class="section-shell py-12" id="nightlife">
        @if($nightlifeCards->isNotEmpty())
            <div>
                <div class="home-section-head mb-7">
                    <div>
                        <h2>السهرات الليلية</h2>
                        <p class="mt-2 text-sm text-slate-500">أجواء ليلية وعروض موسيقية وتجارب اجتماعية مميزة.</p>
                    </div>
                    <a href="#upcoming-events" class="shrink-0 text-lg font-bold text-slate-500 md:text-xl">مشاهدة النتائج ‹</a>
                </div>
                <div class="home-card-grid">
                    @foreach($nightlifeCards as $card)
                        {!! $renderCard($card, true) !!}
                    @endforeach
                </div>
            </div>
        @endif

        @if($arabicGuideCards->isNotEmpty())
            <div class="{{ $nightlifeCards->isNotEmpty() ? 'mt-16' : '' }}">
                <div class="home-section-head mb-7">
                    <div>
                        <h2>دليل الفعاليات العربية</h2>
                        <p class="mt-2 text-sm text-slate-500">فعاليات محلية وعربية قابلة للإدارة من لوحة المحتوى مباشرة.</p>
                    </div>
                    <a href="#upcoming-events" class="shrink-0 text-lg font-bold text-slate-500 md:text-xl">النتائج الكاملة ‹</a>
                </div>
                <div class="home-card-grid">
                    @foreach($arabicGuideCards as $card)
                        {!! $renderCard($card, true) !!}
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endif

@if($theatreCards->isNotEmpty() || $nearbyEntertainmentCards->isNotEmpty())
    <section class="section-shell py-12">
        @if($theatreCards->isNotEmpty())
            <div>
                <div class="home-section-head mb-8">
                    <div>
                        <h2>العروض والمسرحيات</h2>
                        <p class="mt-2 text-sm text-slate-500">العروض المسرحية والكوميدية وتجارب المسرح الحي في عرض مرتب مثل بقية الأقسام.</p>
                    </div>
                    <a href="#upcoming-events" class="shrink-0 text-lg font-bold text-slate-500 md:text-xl">مشاهدة النتائج ‹</a>
                </div>
                <div class="home-card-grid">
                    @foreach($theatreCards as $card)
                        {!! $renderCard($card, true) !!}
                    @endforeach
                </div>
            </div>
        @endif

        @if($nearbyEntertainmentCards->isNotEmpty())
            <div class="{{ $theatreCards->isNotEmpty() ? 'mt-16' : '' }}">
                <div class="home-section-head mb-8">
                    <div>
                        <h2>فعاليات قريبة منك</h2>
                        <p class="mt-2 text-sm text-slate-500">فعاليات مقترحة من المدن القريبة بنفس أسلوب العرض الشبكي الواضح.</p>
                    </div>
                    <a href="#upcoming-events" class="shrink-0 text-lg font-bold text-slate-500 md:text-xl">النتائج الكاملة ‹</a>
                </div>
                <div class="home-card-grid">
                    @foreach($nearbyEntertainmentCards as $card)
                        {!! $renderCard($card, true) !!}
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endif

@if($cityCircles->isNotEmpty() || $otherTags->isNotEmpty())
    <section class="section-shell py-12">
        <div class="home-collection-panel p-5 md:p-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_.9fr] lg:items-start">
                @if($cityCircles->isNotEmpty())
                    <div>
                        <div class="home-section-head">
                            <div>
                                <p class="text-xs font-black text-violet-700">مدن نشطة</p>
                                <h2>المدن القريبة مع الفعاليات المقامة حالياً</h2>
                                <p class="mt-2 text-sm text-slate-500">اختصارات مباشرة للمدن التي تحتوي على تجارب وفعاليات قابلة للحجز.</p>
                            </div>
                        </div>
                        <div class="home-horizontal-row mt-7 sm:grid sm:grid-cols-2">
                            @foreach($cityCircles as $city)
                                <a href="{{ route('homepage-items.show', $city) }}" class="home-city-chip">
                                    <img src="{{ $city->image_url ?: $fallbackImage }}" alt="{{ $city->title }}">
                                    <div>
                                        <p class="text-xl font-black text-slate-950">{{ $city->title }}</p>
                                        <p class="mt-1 text-xs font-bold text-slate-400">فعاليات وتجارب متاحة</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($otherTags->isNotEmpty())
                    <div class="rounded-[28px] bg-white p-5 shadow-[0_16px_42px_rgba(15,23,42,.06)]">
                        <p class="text-xs font-black text-violet-700">تصفح سريع</p>
                        <h3 class="mt-1 text-3xl font-black text-slate-950 md:text-4xl">الفئات الأخرى</h3>
                        <div class="home-horizontal-row mt-6 sm:flex sm:flex-wrap">
                            @foreach($otherTags as $tag)
                                <a href="{{ route('homepage-items.show', $tag) }}" class="home-tag-chip rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">{{ $tag->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

<section class="section-shell py-16" id="upcoming-events">
    <div class="home-section-head">
        <div>
            <h2>الفعاليات القادمة</h2>
            <p class="mt-2 text-sm text-slate-500">نتائج حقيقية من قاعدة البيانات حسب المدينة والتصنيف والتاريخ المختار.</p>
        </div>
        <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700">
            {{ $events->total() }} فعالية متاحة
        </div>
    </div>

    <div class="home-card-grid three-up mt-10">
        @forelse($upcomingEventCards as $card)
            {!! $renderCard($card) !!}
        @empty
            <div class="col-span-full rounded-[2rem] border border-dashed border-slate-200 px-6 py-12 text-center text-slate-500">
                لا توجد فعاليات مطابقة للفلاتر الحالية. جرّب تغيير المدينة أو التصنيف أو التاريخ.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $events->links() }}
    </div>
</section>

<section class="section-shell py-16">
    <div class="home-dark-band overflow-hidden p-6 text-white md:p-9">
        <div class="grid gap-8 lg:grid-cols-[1.05fr_.95fr] lg:items-center">
            <div>
                <p class="w-fit rounded-full bg-white/10 px-4 py-2 text-xs font-black text-fuchsia-100">تجربة حجز موثوقة</p>
                <h2 class="mt-5 text-3xl font-black leading-tight md:text-5xl">من الاكتشاف للحجز في خطوات واضحة.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/70 md:text-base">كل البانرات والأقسام والفعاليات مرتبطة بلوحة التحكم، والنتائج المعروضة هنا تأتي من نفس قاعدة البيانات المستخدمة في التطبيق.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach([
                    ['icon' => '🛡', 'title' => 'شراء آمن', 'text' => 'بوابات دفع ومراجعة واضحة'],
                    ['icon' => '🎫', 'title' => 'تذاكر فورية', 'text' => 'باركود وبيانات حجز جاهزة'],
                    ['icon' => '💳', 'title' => 'طرق دفع متعددة', 'text' => 'خيارات مناسبة لكل عميل'],
                    ['icon' => '👥', 'title' => 'دعم ومتابعة', 'text' => 'ربط كامل مع لوحة الإدارة'],
                ] as $feature)
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <div class="text-3xl">{{ $feature['icon'] }}</div>
                        <h3 class="mt-3 text-lg font-black">{{ $feature['title'] }}</h3>
                        <p class="mt-1 text-sm text-white/60">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
