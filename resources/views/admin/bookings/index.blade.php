@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الحجوزات والطلبات';
    $pageDescription = 'مراجعة الحجوزات، البحث المتقدم، تتبع الدفع، والدخول إلى تفاصيل كل طلب.';
    $visibleBookings = $bookings->getCollection();
    $visibleTickets = $visibleBookings->sum(fn ($booking) => $booking->items->sum('quantity'));
    $visibleRevenue = $visibleBookings->sum('total_amount');
    $paidRatio = ($statusCounts['all'] ?? 0) > 0 ? round((($statusCounts['paid'] ?? 0) / $statusCounts['all']) * 100) : 0;
@endphp

@push('styles')
    <style>
        .booking-ops-hero {
            background:
                radial-gradient(circle at 12% 20%, rgba(109, 40, 217, .14), transparent 24rem),
                radial-gradient(circle at 92% 20%, rgba(232, 53, 109, .10), transparent 22rem),
                linear-gradient(135deg, rgba(255,255,255,.98), rgba(248,250,252,.96));
        }
        .booking-metric {
            position: relative;
            overflow: hidden;
            min-height: 116px;
        }
        .booking-metric::after {
            content: '';
            position: absolute;
            inset-inline-start: 0;
            inset-block: 18px;
            width: 4px;
            border-radius: 999px;
            background: var(--metric-color, #6d28d9);
        }
        .booking-card {
            border-color: rgba(124, 58, 237, .16);
        }
        .booking-card:hover {
            border-color: rgba(124, 58, 237, .32);
        }
        .booking-ref {
            font-family: 'Outfit', 'Cairo', sans-serif;
            letter-spacing: .02em;
        }
        .booking-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: .42rem .72rem;
            font-size: 12px;
            font-weight: 900;
        }
        .booking-pill::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: currentColor;
            opacity: .8;
        }
        .booking-money {
            background: linear-gradient(135deg, #0f172a, #37206f);
        }
    </style>
@endpush

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head booking-ops-hero">
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-stretch">
            <div class="flex flex-col justify-between gap-5">
                <div>
                <span class="admin-page-kicker">العمليات اليومية</span>
                    <h2 class="admin-page-title">مركز عمليات الحجوزات والتذاكر</h2>
                    <p class="admin-page-description">تابع كل طلب من لحظة الدفع حتى إرسال التذاكر، مع مؤشرات فورية للتذاكر المباعة، قيمة الطلبات، وحالات الدفع.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.bookings.export.csv', request()->query()) }}" class="admin-secondary-btn">CSV</a>
                    <a href="{{ route('admin.bookings.export.xlsx', request()->query()) }}" class="admin-success-btn">XLSX</a>
                    <a href="{{ route('admin.bookings.export.pdf', request()->query()) }}" target="_blank" class="admin-danger-btn !bg-rose-50 !text-rose-700 !border !border-rose-200 !shadow-none">PDF</a>
                    <a href="{{ route('admin.bookings.export.print', request()->query()) }}" target="_blank" class="admin-secondary-btn">Print</a>
                    <div class="topbar-chip">المعروض: {{ $bookings->total() }} طلب</div>
                </div>
            </div>

            <div class="booking-money rounded-[1.6rem] p-5 text-white shadow-2xl shadow-violet-900/10">
                <p class="text-sm font-bold text-white/70">ملخص الصفحة الحالية</p>
                <div class="mt-4 grid gap-3">
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-xs text-white/65">قيمة الطلبات المعروضة</p>
                        <p class="mt-2 text-3xl font-black">{{ number_format($visibleRevenue, 2) }} ر.س</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs text-white/65">تذاكر</p>
                            <p class="mt-2 text-2xl font-black">{{ number_format($visibleTickets) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs text-white/65">تحصيل</p>
                            <p class="mt-2 text-2xl font-black">{{ $paidRatio }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-tile booking-metric p-5" style="--metric-color:#0f172a"><p class="text-sm font-bold text-slate-500">كل الحجوزات</p><p class="mt-3 text-4xl font-black">{{ $statusCounts['all'] }}</p><p class="mt-2 text-xs font-bold text-slate-400">إجمالي الطلبات المسجلة</p></div>
        <div class="stat-tile booking-metric p-5" style="--metric-color:#059669"><p class="text-sm font-bold text-slate-500">مدفوعة</p><p class="mt-3 text-4xl font-black text-emerald-700">{{ $statusCounts['paid'] }}</p><p class="mt-2 text-xs font-bold text-emerald-600">طلبات جاهزة للتذاكر</p></div>
        <div class="stat-tile booking-metric p-5" style="--metric-color:#e11d48"><p class="text-sm font-bold text-slate-500">ملغاة</p><p class="mt-3 text-4xl font-black text-rose-700">{{ $statusCounts['cancelled'] }}</p><p class="mt-2 text-xs font-bold text-rose-600">تحتاج متابعة خدمة عملاء</p></div>
        <div class="stat-tile booking-metric p-5" style="--metric-color:#d97706"><p class="text-sm font-bold text-slate-500">مسترجعة</p><p class="mt-3 text-4xl font-black text-amber-700">{{ $statusCounts['refunded'] }}</p><p class="mt-2 text-xs font-bold text-amber-600">عمليات تمت معالجتها</p></div>
    </div>

    <div class="admin-filter-panel">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-black text-slate-900">فلترة الحجوزات</h3>
                <p class="mt-1 text-xs font-bold text-slate-500">استخدم البحث مع الحالات والبوابات للوصول لأي تذكرة بسرعة.</p>
            </div>
            @if(request()->query())
                <span class="booking-pill bg-violet-50 text-violet-700">فلتر نشط</span>
            @endif
        </div>
        <form method="GET" class="grid gap-4 md:grid-cols-2 xl:grid-cols-12">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="مرجع الحجز / العميل / البريد / الفعالية" class="rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-4">

            <select name="status" class="rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-2">
                <option value="">كل حالات الحجز</option>
                @foreach($bookingStatuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>

            <select name="payment_status" class="rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-2">
                <option value="">كل حالات الدفع</option>
                @foreach($paymentStatuses as $status)
                    <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $status }}</option>
                @endforeach
            </select>

            <select name="gateway" class="rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-2">
                <option value="">كل البوابات</option>
                @foreach($gateways as $gateway)
                    <option value="{{ $gateway }}" @selected(request('gateway') === $gateway)>{{ strtoupper($gateway) }}</option>
                @endforeach
            </select>

            <div class="flex gap-3 xl:col-span-2">
                <button class="admin-primary-btn flex-1">فلترة</button>
                <a href="{{ route('admin.bookings.index') }}" class="admin-secondary-btn">إعادة</a>
            </div>

            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-2">
        </form>
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
            @php
                $ticketsCount = $booking->items->sum('quantity');
                $statusClass = in_array($booking->status, ['paid', 'completed']) ? 'bg-emerald-50 text-emerald-700' : ($booking->status === 'cancelled' ? 'bg-rose-50 text-rose-700' : ($booking->status === 'refunded' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600'));
                $paymentClass = $booking->payment_status === 'paid' ? 'bg-cyan-50 text-cyan-700' : ($booking->payment_status === 'failed' ? 'bg-rose-50 text-rose-700' : ($booking->payment_status === 'refunded' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600'));
            @endphp
            <article class="admin-card interactive-card booking-card overflow-hidden p-0">
                <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_260px]">
                    <div class="p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="booking-ref rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $booking->reference }}</span>
                            <span class="booking-pill {{ $statusClass }}">{{ $booking->status }}</span>
                            <span class="booking-pill {{ $paymentClass }}">{{ $booking->payment_status }}</span>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-end">
                            <div>
                                <h2 class="text-xl font-black leading-snug text-slate-950">{{ $booking->event?->title ?? 'فعالية غير متاحة' }}</h2>
                                <p class="mt-1 text-sm font-bold text-slate-500">{{ $booking->user?->name ?? 'عميل غير مسجل' }} • {{ $booking->customer_email }} • {{ $booking->customer_phone }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-bold text-slate-500">إجمالي الطلب</p>
                                <p class="mt-1 text-2xl font-black text-slate-950">{{ number_format($booking->total_amount, 2) }} <span class="text-sm">ر.س</span></p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-4">
                            <div class="rounded-2xl border border-slate-100 px-4 py-3"><p class="text-xs text-slate-400">التذاكر</p><p class="mt-1 font-black text-slate-900">{{ $ticketsCount }}</p></div>
                            <div class="rounded-2xl border border-slate-100 px-4 py-3"><p class="text-xs text-slate-400">البوابة</p><p class="mt-1 font-black text-slate-900">{{ strtoupper($booking->latestPayment?->gateway ?? 'N/A') }}</p></div>
                            <div class="rounded-2xl border border-slate-100 px-4 py-3"><p class="text-xs text-slate-400">التاريخ</p><p class="mt-1 font-black text-slate-900">{{ $booking->booking_date?->translatedFormat('d M Y') }}</p></div>
                            <div class="rounded-2xl border border-slate-100 px-4 py-3"><p class="text-xs text-slate-400">الكوبون</p><p class="mt-1 font-black text-slate-900">{{ $booking->coupon?->code ?? 'لا يوجد' }}</p></div>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between gap-4 border-t border-slate-100 bg-slate-50/70 p-5 xl:border-t-0 xl:border-r">
                        <div>
                            <p class="text-xs font-black text-slate-400">ملخص التذاكر</p>
                            <div class="mt-3 space-y-2">
                                @foreach($booking->items->take(3) as $item)
                                    <div class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 text-xs font-bold">
                                        <span class="truncate text-slate-700">{{ $item->ticket_name }}</span>
                                        <span class="text-slate-950">x{{ $item->quantity }}</span>
                                    </div>
                                @endforeach
                                @if($booking->items->count() > 3)
                                    <p class="text-xs font-bold text-slate-400">+ {{ $booking->items->count() - 3 }} نوع آخر</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="admin-primary-btn flex-1">التفاصيل</a>
                            <form method="POST" action="{{ route('admin.bookings.resend', $booking) }}" class="flex-1">
                                @csrf
                                <button class="admin-secondary-btn w-full">إعادة إرسال</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-empty">لا توجد حجوزات مطابقة للبحث الحالي.</div>
        @endforelse
    </div>

    <div>{{ $bookings->links() }}</div>
</section>
@endsection
