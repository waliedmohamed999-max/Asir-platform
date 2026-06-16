<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($appSettings['platform_name'] ?? 'منصة عسير') }}</title>
    <meta name="description" content="{{ $description ?? ($appSettings['seo_meta_description'] ?? 'منصة عسير لحجز التذاكر والفعاليات في السعودية') }}">
    <link rel="icon" type="image/png" href="{{ $appSettings['platform_favicon_url'] ?? asset('branding/aseer-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ $appSettings['platform_favicon_url'] ?? asset('branding/aseer-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: Tahoma, 'Segoe UI', sans-serif; background: #f6f8fc; color: #15172c; }
        .section-shell { max-width: 1240px; margin-inline: auto; padding-inline: 16px; }
        .soft-card { background: #fff; border: 1px solid #eef0f6; border-radius: 32px; box-shadow: 0 8px 30px rgba(15, 23, 42, .06); }
        .nav-pill { border: 1px solid #eceef4; border-radius: 9999px; background: #fff; box-shadow: 0 6px 20px rgba(15, 23, 42, .06); }
        .site-header { background: rgba(255,255,255,.96); backdrop-filter: blur(14px); box-shadow: 0 10px 30px rgba(15, 23, 42, .04); }
        .site-nav-link { white-space: nowrap; color: #0f172a; transition: .18s ease; }
        .site-nav-link:hover { color: #6d28d9; }
        .site-header-grid { display: grid; gap: 14px; align-items: center; }
        .site-search { height: 42px; border-radius: 999px; border: 1px solid #e4e9f2; background: #fff; box-shadow: 0 8px 26px rgba(15, 23, 42, .05); }
        @media (min-width: 1024px) {
            .site-header-grid { grid-template-columns: 210px minmax(0, 1fr) 210px; }
        }
        @media (max-width: 767px) {
            body { background: #f4f7fb; }
            .section-shell { padding-inline: 12px; }
            .site-header { position: sticky; }
            .site-header .section-shell { padding-block: 8px; }
            .site-header-grid {
                grid-template-columns: 96px minmax(0, 1fr);
                grid-template-areas:
                    "logo tools"
                    "search search"
                    "nav nav";
                gap: 9px 10px;
            }
            .site-logo-link { grid-area: logo; justify-content: flex-start; min-width: 0; }
            .site-logo-link img { height: 34px; max-width: 90px; }
            .site-search-wrap { grid-area: search; }
            .site-tools { grid-area: tools; justify-content: flex-end; min-width: 0; gap: 8px; direction: rtl; }
            .site-tools .site-currency { max-width: 96px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; direction: ltr; }
            .site-tools .site-account-icon { width: 32px; height: 32px; flex: 0 0 32px; }
            .site-search { height: 38px; box-shadow: none; }
            .site-search input { font-size: 12px; text-align: right; }
            .site-header nav {
                grid-area: nav;
                justify-content: flex-start;
                gap: 8px;
                margin-top: 0;
                padding: 0 2px 5px;
                scrollbar-width: none;
                scroll-padding-inline: 12px;
                overflow-y: hidden;
            }
            .site-header nav::-webkit-scrollbar { display: none; }
            .site-nav-link {
                display: inline-flex;
                min-height: 32px;
                align-items: center;
                flex: 0 0 auto;
                border-radius: 999px;
                background: #f1f5fb;
                padding: 0 11px;
                font-size: 11.5px;
                border: 1px solid #e7edf6;
            }
        }
    </style>
    @stack('styles')
    @livewireStyles
</head>
<body>
    <header class="site-header sticky top-0 z-50 border-b border-slate-200">
        <div class="section-shell py-3">
            <div class="site-header-grid">
                <a href="{{ route('home') }}" class="site-logo-link flex items-center justify-center gap-3 lg:justify-start">
                    <img src="{{ $appSettings['platform_logo_url'] ?? asset('branding/aseer-logo.png') }}" alt="شعار {{ $appSettings['platform_name'] ?? 'منصة عسير' }}" class="h-10 w-auto object-contain lg:h-12">
                </a>

                <div class="site-search-wrap min-w-0">
                    <div class="mx-auto flex max-w-3xl items-center gap-3">
                        <div class="hidden items-center gap-2 text-[13px] font-bold text-slate-600 sm:flex">
                            <span>جدة</span>
                            <span class="text-xs">📍</span>
                        </div>
                        <div class="site-search flex min-w-0 flex-1 items-center gap-3 px-4">
                            <span class="text-[12px] text-slate-400">📅</span>
                            <input type="text" placeholder="البحث عن الفعالية أو الفئة" class="w-full border-0 p-0 text-[14px] text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                            <span class="text-[12px] text-emerald-400">✦</span>
                        </div>
                    </div>
                    <nav class="mt-3 flex items-center justify-center gap-5 overflow-x-auto pb-1 text-[13px] font-black lg:gap-7 lg:pb-0 lg:text-[14px]">
                        <a class="site-nav-link" href="{{ route('home') }}">الفعاليات</a>
                        <a class="site-nav-link" href="#nightlife">المغامرات والتجارب</a>
                        <a class="site-nav-link" href="#upcoming-events">الفعاليات القادمة</a>
                        <a class="site-nav-link" href="#new-in-jeddah">ما الجديد في جدة؟</a>
                        @auth
                            <a class="site-nav-link" href="{{ route('dashboard') }}">حسابي</a>
                        @else
                            <a class="site-nav-link" href="{{ route('login') }}">تسجيل الدخول</a>
                        @endauth
                    </nav>
                </div>

                <div class="site-tools flex items-center justify-center gap-3 lg:justify-end">
                    <div class="site-currency text-[13px] font-bold text-slate-500">🌐 {{ $appSettings['default_currency'] ?? 'SAR' }} / {{ strtoupper($appSettings['default_locale'] ?? 'AR') }}</div>
                    <div class="site-account-icon flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8zm-7 15a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="section-shell mt-4">
            <div class="rounded-2xl bg-emerald-100 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @php
        $supportEmail = $appSettings['support_email'] ?? null;
        $supportWhatsapp = !empty($appSettings['support_whatsapp']) ? ('https://wa.me/' . preg_replace('/\D+/', '', $appSettings['support_whatsapp'])) : null;
        $enabledPayments = [];
        $footerPages = $footerPages ?? collect();

        if (!empty($appSettings['payment_mada_enabled']) && filter_var($appSettings['payment_mada_enabled'], FILTER_VALIDATE_BOOLEAN)) {
            $enabledPayments[] = ['label' => 'mada', 'class' => 'text-sm leading-tight text-slate-700'];
        }

        if (!empty($appSettings['payment_stripe_enabled']) && filter_var($appSettings['payment_stripe_enabled'], FILTER_VALIDATE_BOOLEAN)) {
            $enabledPayments[] = ['label' => 'VISA', 'class' => 'text-blue-700'];
            $enabledPayments[] = ['label' => 'Apple Pay', 'class' => ''];
        }

        if (!empty($appSettings['payment_paypal_enabled']) && filter_var($appSettings['payment_paypal_enabled'], FILTER_VALIDATE_BOOLEAN)) {
            $enabledPayments[] = ['label' => 'PayPal', 'class' => 'text-sky-700'];
        }

        $socialLinks = [
            ['url' => $appSettings['social_instagram'] ?? null, 'label' => 'Instagram', 'icon' => '◐'],
            ['url' => $appSettings['social_snapchat'] ?? null, 'label' => 'Snapchat', 'icon' => 'f'],
            ['url' => $appSettings['social_x'] ?? null, 'label' => 'X', 'icon' => 'X'],
            ['url' => $appSettings['social_tiktok'] ?? null, 'label' => 'TikTok', 'icon' => '✈'],
        ];

        $footerSectionTitles = [
            'categories' => $appSettings['footer_categories_title'] ?? 'الفئات',
            'about' => $appSettings['footer_about_title'] ?? 'من نحن',
            'organizers' => $appSettings['footer_organizers_title'] ?? 'للمنظمين',
            'services' => $appSettings['footer_services_title'] ?? 'الخدمات',
            'partners' => $appSettings['footer_partners_title'] ?? 'للشركاء',
            'apps' => $appSettings['footer_apps_title'] ?? 'تحميل التطبيق',
        ];

        $footerFallbacks = [
            'categories' => [
                ['label' => 'أبرز الفعاليات'],
                ['label' => 'العالم السياحية المميزة'],
                ['label' => 'السهرات الليلية'],
                ['label' => 'نوادي الشاطئ'],
                ['label' => 'دليل الفعاليات العربية'],
                ['label' => 'إظهار الكل'],
            ],
            'about' => [
                ['label' => 'انضم لفريقنا'],
                ['label' => 'الأسعار'],
                ['label' => 'مدونة ' . ($appSettings['platform_name'] ?? 'عسير')],
                ['label' => 'آخر الأخبار'],
                ['label' => 'الشروط والأحكام', 'url' => route('pages.show', 'terms-and-conditions')],
                ['label' => 'مركز المساعدة', 'url' => route('faq')],
                ['label' => 'خريطة الموقع'],
            ],
            'organizers' => [
                ['label' => 'نظرة عامة'],
                ['label' => 'الفعاليات الترفيهية'],
                ['label' => 'المغامرات والتجارب الاستثنائية'],
                ['label' => 'فعاليات قطاع الأعمال'],
                ['label' => 'الأنشطة والأحداث الرياضية'],
                ['label' => 'حلول تذاكر الفعاليات'],
                ['label' => 'مميزات التذاكر'],
                ['label' => 'دليل المنظمين'],
            ],
            'services' => [
                ['label' => 'خدمات إدارة الفعاليات'],
                ['label' => 'خدمات التسويق'],
                ['label' => 'فريق إدارة التذاكر للفعالية'],
                ['label' => 'طباعة التذاكر'],
                ['label' => 'خدمة إصدار التراخيص بشكل سريع'],
            ],
            'partners' => [
                ['label' => 'برنامج التسويق بالعمولة'],
            ],
            'apps' => [
                ['label' => 'Google Play'],
                ['label' => 'App Store'],
                ['label' => 'AppGallery'],
            ],
        ];
    @endphp

    <footer class="mt-24 border-t border-slate-200 bg-white">
        <div class="section-shell py-10">
            <div class="border-b border-slate-200 pb-8">
                <div class="grid gap-8 text-center lg:grid-cols-4 lg:text-right">
                    <div class="lg:order-4">
                        <div class="flex items-center justify-center gap-3 lg:justify-start">
                            <img src="{{ $appSettings['platform_logo_url'] ?? asset('branding/aseer-logo.png') }}" alt="شعار {{ $appSettings['platform_name'] ?? 'منصة عسير' }}" class="h-14 w-auto object-contain">
                        </div>
                        <p class="mt-4 text-sm text-slate-600">{{ $appSettings['footer_about'] ?? 'منصة اكتشاف وتسويق المحتوى الترفيهي' }}</p>
                    </div>

                    <div class="lg:order-3">
                        <h3 class="text-[15px] font-black">{{ $appSettings['support_section_title'] ?? 'هل لديك أي أسئلة أو استفسارات أخرى؟' }}</h3>
                        <p class="mt-1 text-[15px] font-black">{{ $appSettings['support_section_subtitle'] ?? 'يسعدنا تواصلك معنا' }}</p>
                        <div class="mt-4 flex items-center justify-center gap-3 lg:justify-start">
                            @if($supportEmail)
                                <a href="mailto:{{ $supportEmail }}" class="rounded-full border border-slate-900 px-5 py-2 text-sm font-bold text-slate-900">{{ $appSettings['support_button_text'] ?? 'فريق الدعم' }}</a>
                            @else
                                <button class="rounded-full border border-slate-900 px-5 py-2 text-sm font-bold text-slate-900">{{ $appSettings['support_button_text'] ?? 'فريق الدعم' }}</button>
                            @endif
                            @if($supportWhatsapp)
                                <a href="{{ $supportWhatsapp }}" target="_blank" rel="noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500 text-white">◔</a>
                            @endif
                        </div>
                    </div>

                    <div class="lg:order-2">
                        <h3 class="text-[15px] font-black">{{ $appSettings['customer_service_title'] ?? 'خدمة العملاء' }}</h3>
                        <p class="mt-2 text-[30px] font-black leading-none text-slate-900">{{ $appSettings['support_phone'] ?? '920008640' }}</p>
                        <p class="mt-2 text-sm text-slate-500">الأحد - الخميس 9:00 - 17:00</p>
                    </div>

                    <div class="lg:order-1">
                        <h3 class="text-[15px] font-black">{{ $appSettings['payment_section_title'] ?? 'نقبل طرق الدفع التالية' }}</h3>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-3 text-2xl font-black text-slate-800 lg:justify-start">
                            @forelse($enabledPayments as $payment)
                                <span class="{{ $payment['class'] }}">{{ $payment['label'] }}</span>
                            @empty
                                <span class="text-sm font-semibold text-slate-400">لا توجد بوابات دفع مفعلة حالياً</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-10 py-8 lg:grid-cols-[1.1fr_1fr_1fr_1fr_1fr_1fr]">
                @foreach(['categories', 'about', 'organizers', 'services', 'partners', 'apps'] as $groupKey)
                    @php
                        $groupPages = $footerPages->get($groupKey, collect());
                        $groupItems = $groupPages->isNotEmpty()
                            ? $groupPages->map(fn ($page) => [
                                'label' => $page->footer_label ?: $page->title,
                                'url' => $page->publicUrl(),
                                'blank' => $page->open_in_new_tab,
                            ])->values()->all()
                            : ($footerFallbacks[$groupKey] ?? []);
                    @endphp
                    <div>
                        <h3 class="text-[15px] font-black">{{ $footerSectionTitles[$groupKey] }}</h3>
                        @if($groupKey === 'apps')
                            <div class="mt-4 space-y-3">
                                @foreach($groupItems as $item)
                                    @if(!empty($item['url']))
                                        <a href="{{ $item['url'] }}" @if(!empty($item['blank'])) target="_blank" rel="noreferrer" @endif class="block w-fit rounded-lg bg-black px-4 py-2 text-sm font-bold text-white">{{ $item['label'] }}</a>
                                    @else
                                        <div class="w-fit rounded-lg bg-black px-4 py-2 text-sm font-bold text-white">{{ $item['label'] }}</div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 space-y-2.5 text-sm text-slate-600">
                                @foreach($groupItems as $item)
                                    <p>
                                        @if(!empty($item['url']))
                                            <a href="{{ $item['url'] }}" @if(!empty($item['blank'])) target="_blank" rel="noreferrer" @endif class="transition hover:text-slate-900">{{ $item['label'] }}</a>
                                        @else
                                            {{ $item['label'] }}
                                        @endif
                                    </p>
                                @endforeach
                            </div>
                            @if($groupKey === 'services')
                                <button class="mt-4 rounded-full border border-slate-900 px-6 py-2 text-sm font-bold text-slate-900">{{ $appSettings['organizer_cta_text'] ?? 'إضافة فعالية' }}</button>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col items-center justify-between gap-5 border-t border-slate-200 pt-8 text-center lg:flex-row lg:text-right">
                <div class="flex items-center gap-4">
                    @if($supportEmail)
                        <a href="mailto:{{ $supportEmail }}" class="rounded-full border border-slate-900 px-8 py-2 text-sm font-bold text-slate-900">{{ $appSettings['footer_support_button_text'] ?? 'مركز الدعم' }}</a>
                    @else
                        <button class="rounded-full border border-slate-900 px-8 py-2 text-sm font-bold text-slate-900">{{ $appSettings['footer_support_button_text'] ?? 'مركز الدعم' }}</button>
                    @endif
                    <p class="text-sm font-bold text-slate-900">{{ $appSettings['support_section_title'] ?? 'هل لديك أي أسئلة أو استفسارات أخرى؟' }} يُرجى زيارة</p>
                </div>
                <div class="flex items-center gap-6 text-xl text-slate-800">
                    @foreach($socialLinks as $social)
                        @if(!empty($social['url']))
                            <a href="{{ $social['url'] }}" target="_blank" rel="noreferrer" aria-label="{{ $social['label'] }}">{{ $social['icon'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
    @livewireScripts
</body>
</html>
