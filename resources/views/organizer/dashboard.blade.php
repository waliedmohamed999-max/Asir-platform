@extends('layouts.app')

@section('content')
<section class="section-shell py-10">
    @php
        $eventBadges = [
            'published' => 'bg-emerald-100 text-emerald-700',
            'draft' => 'bg-slate-100 text-slate-700',
            'scheduled' => 'bg-sky-100 text-sky-700',
            'sold_out' => 'bg-amber-100 text-amber-700',
            'ended' => 'bg-slate-200 text-slate-700',
            'cancelled' => 'bg-rose-100 text-rose-700',
        ];

        $paymentBadges = [
            'paid' => 'bg-emerald-100 text-emerald-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'failed' => 'bg-rose-100 text-rose-700',
            'refunded' => 'bg-slate-200 text-slate-700',
        ];
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <div class="soft-card overflow-hidden p-0">
            <div class="grid gap-0 lg:grid-cols-[1.1fr_.9fr]">
                <div class="bg-[linear-gradient(135deg,#1e1b4b_0%,#312e81_40%,#6d28d9_100%)] p-8 text-white lg:p-10">
                    <p class="text-sm font-bold text-violet-200">مرحباً {{ $organizer->name }}</p>
                    <h1 class="mt-3 text-3xl font-black lg:text-[2.4rem]">لوحة المنظم</h1>
                    <p class="mt-4 max-w-2xl text-[14px] leading-7 text-violet-100 lg:text-[15px]">
                        راقب أداء الفعاليات، راجع الإيرادات والحجوزات، وانتقل بسرعة إلى إدارة المحتوى والأسعار من واجهة منظمة وواضحة.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ route('organizer.events.create') }}" class="rounded-full bg-white px-5 py-3 text-sm font-black text-violet-900">إضافة فعالية</a>
                        <a href="{{ route('organizer.events.index') }}" class="rounded-full border border-white/25 px-5 py-3 text-sm font-bold text-white/90">إدارة الفعاليات</a>
                    </div>
                </div>

                <div class="grid gap-4 bg-slate-50 p-6 lg:p-8">
                    <div class="rounded-[1.75rem] bg-white p-5 shadow-sm ring-1 ring-slate-100">
                        <p class="text-sm text-slate-500">الإيرادات المحصلة</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['gross_revenue'], 2) }} ر.س</p>
                        <p class="mt-2 text-sm text-slate-500">إيرادات معلقة: {{ number_format($stats['pending_revenue'], 2) }} ر.س</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            <p class="text-sm text-slate-500">التذاكر المباعة</p>
                            <p class="mt-3 text-xl font-black text-violet-700">{{ $stats['sold_tickets'] }}</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            <p class="text-sm text-slate-500">الفعاليات القادمة</p>
                            <p class="mt-3 text-xl font-black text-sky-700">{{ $stats['upcoming_events'] }}</p>
                        </div>
                    </div>
                    <div class="rounded-[1.5rem] border border-dashed border-violet-200 bg-violet-50/70 p-5">
                        <p class="text-sm text-slate-500">الحجوزات المدفوعة</p>
                        <p class="mt-2 text-xl font-black text-slate-900">{{ $stats['paid_bookings'] }} من أصل {{ $stats['bookings_count'] }} حجز</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">كل الفعاليات</p>
                <p class="mt-3 text-2xl font-black">{{ $stats['events_count'] }}</p>
            </div>
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">المنشورة</p>
                <p class="mt-3 text-2xl font-black text-emerald-700">{{ $stats['published_events'] }}</p>
            </div>
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">المسودات</p>
                <p class="mt-3 text-2xl font-black text-slate-700">{{ $stats['draft_events'] }}</p>
            </div>
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">الحجوزات</p>
                <p class="mt-3 text-2xl font-black text-violet-700">{{ $stats['bookings_count'] }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
        <div class="soft-card p-6 lg:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black">فعالياتي</h2>
                    <p class="mt-1 text-sm text-slate-500">أحدث الفعاليات مع عدد الحجوزات والإيراد المحقق.</p>
                </div>
                <a href="{{ route('organizer.events.index') }}" class="rounded-full border border-violet-100 px-4 py-2 text-sm font-bold text-violet-700">عرض الإدارة الكاملة</a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($events as $event)
                    <div class="rounded-[1.6rem] border border-slate-100 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-black text-slate-900">{{ $event->title }}</h2>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $eventBadges[$event->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $event->status }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">{{ $event->venue_name }} • {{ $event->start_date?->translatedFormat('d M Y - h:i A') }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-sm text-slate-500">الإيراد المدفوع</p>
                                <p class="text-2xl font-black text-slate-900">{{ number_format((float) $event->paid_revenue, 2) }} ر.س</p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">الحجوزات</p>
                                <p class="mt-1 text-lg font-black">{{ $event->bookings_count }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">المدينة</p>
                                <p class="mt-1 text-lg font-black">{{ $event->city?->name ?? 'غير محددة' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-500">الرئيسية</p>
                                <p class="mt-1 text-lg font-black">{{ $event->show_on_homepage ? 'نعم' : 'لا' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-200 p-8 text-center text-slate-500">لا توجد فعاليات بعد. أضف أول فعالية من لوحة المنظم.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $events->links() }}</div>
        </div>

        <div class="soft-card p-6 lg:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black">آخر الحجوزات</h2>
                    <p class="mt-1 text-sm text-slate-500">آخر الطلبات الواردة على فعالياتك.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($recentBookings as $booking)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-black text-slate-900">{{ $booking->user?->name ?? 'عميل' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $booking->event?->title ?? 'فعالية محذوفة' }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $paymentBadges[$booking->payment_status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $booking->payment_status }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3 text-sm text-slate-500">
                            <span>{{ $booking->reference }}</span>
                            <span class="font-black text-slate-900">{{ number_format($booking->total_amount, 2) }} ر.س</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-200 p-8 text-center text-slate-500">لا توجد حجوزات حديثة بعد.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
