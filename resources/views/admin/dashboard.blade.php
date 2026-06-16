@extends('layouts.admin')

@php
    $pageTitle = 'لوحة الإدارة';
    $pageDescription = 'مركز قيادة موحد لمتابعة الأداء التجاري، العمليات اليومية، والمحتوى من شاشة واحدة.';

    $quickStats = [
        ['label' => 'إجمالي الفعاليات', 'value' => $stats['events'], 'hint' => 'كل الفعاليات داخل النظام', 'tone' => 'slate'],
        ['label' => 'التذاكر المباعة', 'value' => number_format($stats['tickets_sold']), 'hint' => 'المباعة عبر كل القنوات', 'tone' => 'violet'],
        ['label' => 'إجمالي الإيرادات', 'value' => number_format($stats['sales'], 2) . ' ر.س', 'hint' => 'الإيرادات المحصلة فقط', 'tone' => 'emerald'],
        ['label' => 'المستخدمون', 'value' => number_format($stats['users']), 'hint' => 'بما فيهم العملاء والمنظمون', 'tone' => 'sky'],
        ['label' => 'المنظمون', 'value' => number_format($stats['organizers']), 'hint' => 'الحسابات المنظِّمة النشطة', 'tone' => 'amber'],
        ['label' => 'متوسط الطلب', 'value' => number_format($stats['average_order_value'], 2) . ' ر.س', 'hint' => 'متوسط سلة الحجز المدفوع', 'tone' => 'fuchsia'],
    ];

    $cadenceStats = [
        ['label' => 'حجوزات اليوم', 'value' => $stats['bookings_today'], 'sub' => number_format($stats['today_revenue'], 2) . ' ر.س'],
        ['label' => 'حجوزات هذا الأسبوع', 'value' => $stats['bookings_this_week'], 'sub' => 'وتيرة أسبوعية'],
        ['label' => 'حجوزات هذا الشهر', 'value' => $stats['bookings_this_month'], 'sub' => number_format($stats['month_revenue'], 2) . ' ر.س'],
        ['label' => 'معدل التحويل', 'value' => $stats['conversion_rate'] . '%', 'sub' => 'من الحجوزات إلى مدفوعات'],
    ];

    $commercialStats = [
        ['label' => 'فعاليات منشورة', 'value' => $stats['published_events']],
        ['label' => 'فعاليات قادمة', 'value' => $stats['upcoming_events']],
        ['label' => 'فعاليات منتهية', 'value' => $stats['ended_events']],
        ['label' => 'فعاليات Draft', 'value' => $stats['draft_events']],
        ['label' => 'مدفوعات معلقة', 'value' => $stats['pending_payments']],
        ['label' => 'مدفوعات فاشلة', 'value' => $stats['failed_payments']],
        ['label' => 'إعلانات نشطة', 'value' => $stats['active_ads']],
        ['label' => 'كوبونات فعالة', 'value' => $stats['active_coupons']],
    ];

    $toneClasses = [
        'slate' => 'from-slate-900 via-slate-800 to-slate-700 text-white',
        'violet' => 'from-violet-700 via-fuchsia-600 to-violet-500 text-white',
        'emerald' => 'from-emerald-600 via-emerald-500 to-teal-500 text-white',
        'sky' => 'from-sky-600 via-cyan-500 to-sky-400 text-white',
        'amber' => 'from-amber-500 via-orange-500 to-amber-400 text-slate-950',
        'fuchsia' => 'from-fuchsia-700 via-violet-600 to-fuchsia-500 text-white',
    ];

    $statusToneClasses = [
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-100',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-100',
        'info' => 'bg-sky-50 text-sky-700 border-sky-100',
    ];
@endphp

@push('styles')
    <style>
        .dashboard-page .dashboard-hero {
            background:
                radial-gradient(circle at 8% 12%, rgba(124, 58, 237, .12), transparent 26rem),
                linear-gradient(135deg, rgba(255,255,255,.98), rgba(248,250,252,.96));
        }
        .dashboard-page .dashboard-title {
            letter-spacing: 0;
            max-width: 760px;
        }
        .dashboard-page .compact-panel {
            border-radius: 20px;
            border: 1px solid #e8edf5;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .045);
        }
        .dashboard-page .action-card {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
<section class="dashboard-page space-y-5">
    <div class="admin-card dashboard-hero overflow-hidden p-5 lg:p-7">
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,.65fr)] xl:items-stretch">
            <div class="space-y-4">
                <span class="inline-flex rounded-full bg-violet-50 px-4 py-2 text-sm font-black text-violet-700">ملخص تنفيذي مباشر</span>
                <div>
                    <h2 class="dashboard-title text-2xl font-black leading-[1.25] text-slate-950 lg:text-4xl">لوحة قيادة واضحة لإدارة الحجوزات والفعاليات والإيرادات.</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500 lg:text-[15px]">
                        راقب الأداء التجاري والتشغيلي لحظياً، تابع أحدث الحركة داخل المنصة، واعرف بسرعة أين تحتاج الإدارة إلى تدخل أو تحسين من خلال مؤشرات واضحة ومركزة.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="admin-card-muted p-4 shadow-sm">
                        <p class="text-xs font-bold text-slate-500">إجمالي الحجوزات</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format($stats['bookings']) }}</p>
                        <p class="mt-1 text-xs text-slate-400">كل الحجوزات المسجلة</p>
                    </div>
                    <div class="admin-card-muted p-4 shadow-sm">
                        <p class="text-xs font-bold text-slate-500">إيراد اليوم</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format($stats['today_revenue'], 2) }}</p>
                        <p class="mt-1 text-xs text-slate-400">ر.س محصلة اليوم</p>
                    </div>
                    <div class="admin-card-muted p-4 shadow-sm">
                        <p class="text-xs font-bold text-slate-500">إيراد الشهر</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format($stats['month_revenue'], 2) }}</p>
                        <p class="mt-1 text-xs text-slate-400">ر.س خلال هذا الشهر</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.7rem] bg-gradient-to-br from-slate-950 via-violet-950 to-fuchsia-700 p-5 text-white shadow-2xl shadow-violet-900/10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-white/70">صحة التشغيل الآن</p>
                        <h3 class="mt-2 text-2xl font-black">معدل التحويل {{ $stats['conversion_rate'] }}%</h3>
                    </div>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-black">آخر تحديث {{ now()->format('H:i') }}</span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach($statusOverview as $item)
                        <div class="rounded-[1.25rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <p class="text-xs font-bold text-white/70">{{ $item['label'] }}</p>
                            <p class="mt-2 text-2xl font-black">{{ number_format($item['value']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card p-5 lg:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-bold text-slate-500">إجراءات سريعة</p>
                <h3 class="mt-1 text-2xl font-black">اختصارات الإدارة</h3>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ now()->translatedFormat('l') }}</span>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.events.create') }}" class="action-card rounded-[1.25rem] border border-slate-200 bg-white px-4 py-4 text-center font-black text-slate-800 transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">إضافة فعالية جديدة</a>
            <a href="{{ route('admin.homepage-items.create') }}" class="action-card rounded-[1.25rem] border border-slate-200 bg-white px-4 py-4 text-center font-black text-slate-800 transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">إضافة بنر أو إعلان</a>
            <a href="{{ route('admin.bookings.index') }}" class="action-card rounded-[1.25rem] border border-slate-200 bg-white px-4 py-4 text-center font-black text-slate-800 transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">مراجعة الحجوزات والطلبات</a>
            <a href="{{ route('admin.reports.sales') }}" class="action-card rounded-[1.25rem] border border-slate-200 bg-white px-4 py-4 text-center font-black text-slate-800 transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">فتح تقارير المبيعات</a>
        </div>

        <div class="mt-5 rounded-[1.35rem] bg-slate-50 p-4 lg:p-5">
            <p class="text-xs font-bold text-slate-500">مؤشرات تجارية مختصرة</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($commercialStats as $item)
                    <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                        <span class="text-sm font-bold text-slate-600">{{ $item['label'] }}</span>
                        <span class="text-lg font-black text-slate-900">{{ number_format($item['value']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-6">
        @foreach($quickStats as $item)
            <div class="stat-tile overflow-hidden">
                <div class="h-full bg-gradient-to-l {{ $toneClasses[$item['tone']] ?? $toneClasses['slate'] }} p-5">
                    <p class="text-sm font-bold opacity-90">{{ $item['label'] }}</p>
                    <p class="mt-4 text-2xl font-black lg:text-3xl">{{ $item['value'] }}</p>
                    <p class="mt-2 text-xs opacity-80">{{ $item['hint'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($cadenceStats as $item)
            <div class="compact-panel p-5">
                <p class="text-sm font-bold text-slate-500">{{ $item['label'] }}</p>
                <p class="mt-3 text-3xl font-black text-slate-950">{{ $item['value'] }}</p>
                <p class="mt-2 text-xs text-slate-400">{{ $item['sub'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <div class="admin-card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-black">الإيرادات آخر 6 أشهر</h2>
                    <p class="mt-1 text-sm text-slate-500">منحنى مبسط يساعدك على قراءة الاتجاهات بسرعة.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700">إيراد محصل: {{ number_format($stats['sales'], 2) }} ر.س</span>
                    <a href="{{ route('admin.reports.sales') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700">تفاصيل التقارير</a>
                </div>
            </div>

            <div class="mt-8 flex h-[280px] items-end gap-3 sm:gap-4">
                @php($maxChart = collect($salesChart)->max('total') ?: 1)
                @foreach($salesChart as $point)
                    @php($height = max(24, (int) (($point['total'] / $maxChart) * 225)))
                    <div class="flex flex-1 flex-col items-center justify-end gap-3">
                        <div class="w-full rounded-t-[22px] bg-gradient-to-t from-slate-950 via-violet-700 to-fuchsia-500 shadow-lg shadow-violet-500/10" style="height: {{ $height }}px"></div>
                        <div class="text-center">
                            <p class="text-sm font-black text-slate-800">{{ $point['label'] }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($point['total'], 0) }} ر.س</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card p-6">
                <h2 class="text-xl font-black">بوابات الدفع</h2>
                <div class="mt-5 space-y-3">
                    @forelse($paymentGatewayStats as $gateway)
                        <div class="rounded-[1.35rem] border border-slate-100 px-4 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-black">{{ $gateway['label'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $gateway['count'] }} عمليات</p>
                                </div>
                                <p class="text-sm font-black text-slate-800">{{ number_format($gateway['paid_total'], 2) }} ر.س</p>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty">لا توجد بيانات دفع بعد.</div>
                    @endforelse
                </div>
            </div>

            <div class="admin-card p-6">
                <h2 class="text-xl font-black">حالة التشغيل</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($statusOverview as $item)
                        <div class="rounded-[1.35rem] border px-4 py-3 {{ $statusToneClasses[$item['tone']] ?? 'bg-slate-50 text-slate-700 border-slate-100' }}">
                            <div class="flex items-center justify-between">
                                <p class="font-bold">{{ $item['label'] }}</p>
                                <span class="text-lg font-black">{{ number_format($item['value']) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black">توزيع الحجوزات حسب المدينة</h2>
                <span class="text-xs font-bold text-slate-400">Top 6</span>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($cityStats as $row)
                    <div class="rounded-[1.35rem] bg-slate-50 px-4 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-bold">{{ $row['label'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ number_format($row['revenue'], 2) }} ر.س</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-sm font-black text-slate-900">{{ $row['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات مدن حالياً.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black">حسب التصنيف</h2>
                <span class="text-xs font-bold text-slate-400">Top 6</span>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($categoryStats as $row)
                    <div class="rounded-[1.35rem] bg-slate-50 px-4 py-4">
                        <div class="flex items-center justify-between">
                            <p class="font-bold">{{ $row['label'] }}</p>
                            <span class="text-sm font-black">{{ $row['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات تصنيفات.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black">حسب نوع التذكرة</h2>
                <span class="text-xs font-bold text-slate-400">Top 6</span>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($eventTypeStats as $row)
                    <div class="rounded-[1.35rem] bg-violet-50 px-4 py-4 text-violet-900">
                        <div class="flex items-center justify-between">
                            <p class="font-bold">{{ $row['label'] }}</p>
                            <span class="text-sm font-black">{{ $row['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات لأنواع التذاكر.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr_.9fr]">
        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black">آخر الحجوزات</h2>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm font-bold text-violet-700">إدارة الحجوزات</a>
            </div>
            <div class="mt-6 space-y-4">
                @forelse($recentBookings as $booking)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-bold">{{ $booking->user->name ?? 'مستخدم محذوف' }}</p>
                                <p class="text-sm text-slate-500">{{ $booking->event->title ?? 'فعالية غير متاحة' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $booking->booking_date?->format('Y-m-d h:i A') }}</p>
                            </div>
                            <span class="badge-pill {{ $booking->payment_status === 'paid' ? 'badge-pill-success' : ($booking->payment_status === 'failed' ? 'badge-pill-danger' : 'badge-pill-warning') }}">
                                {{ $booking->payment_status }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-slate-500">{{ $booking->reference }}</p>
                            <p class="font-black">{{ number_format($booking->total_amount, 2) }} ر.س</p>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد حجوزات حديثة.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black">آخر الفعاليات المضافة</h2>
                <a href="{{ route('admin.events.index') }}" class="text-sm font-bold text-violet-700">إدارة الفعاليات</a>
            </div>
            <div class="mt-6 space-y-4">
                @forelse($latestEvents as $event)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-bold">{{ $event->title }}</p>
                                <p class="text-sm text-slate-500">{{ $event->city?->name ?? 'بدون مدينة' }} • {{ $event->category?->name ?? 'بدون تصنيف' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $event->start_date?->format('Y-m-d h:i A') }}</p>
                            </div>
                            <span class="badge-pill {{ $event->status === 'published' ? 'badge-pill-success' : ($event->status === 'sold_out' ? 'badge-pill-danger' : 'badge-pill-warning') }}">
                                {{ $event->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد فعاليات مضافة حتى الآن.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black">أعلى الفعاليات أداءً</h2>
                <span class="text-sm font-bold text-slate-400">حسب الإيراد</span>
            </div>
            <div class="mt-6 space-y-4">
                @forelse($topEvents as $event)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-bold">{{ $event['title'] }}</p>
                                <p class="text-sm text-slate-500">{{ $event['city'] ?: 'بدون مدينة' }} • {{ $event['category'] ?: 'بدون تصنيف' }}</p>
                            </div>
                            <span class="badge-pill {{ $event['status'] === 'published' ? 'badge-pill-success' : 'badge-pill-muted' }}">{{ $event['status'] }}</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $event['bookings'] }} حجوزات</span>
                            <span class="font-black text-slate-900">{{ number_format($event['revenue'], 2) }} ر.س</span>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات أداء كافية بعد.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
