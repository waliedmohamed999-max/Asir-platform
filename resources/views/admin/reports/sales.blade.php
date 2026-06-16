@extends('layouts.admin')

@php
    $pageTitle = 'تقارير المبيعات';
    $pageDescription = 'إيرادات اليوم والمتوسط وأفضل الفعاليات والمدن أداءً مع قائمة الطلبات المدفوعة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">التقارير والتحليلات</span>
                <h2 class="admin-page-title">مركز تقارير المبيعات</h2>
                <p class="admin-page-description">راجع الأداء المالي حسب التاريخ والفعالية والمدينة من شاشة موحدة.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.reports.sales.export.csv', request()->query()) }}" class="admin-secondary-btn">CSV</a>
                <a href="{{ route('admin.reports.sales.export.xlsx', request()->query()) }}" class="admin-success-btn">XLSX</a>
                <a href="{{ route('admin.reports.sales.export.pdf', request()->query()) }}" target="_blank" class="admin-danger-btn !bg-rose-50 !text-rose-700 !border !border-rose-200 !shadow-none">PDF</a>
                <a href="{{ route('admin.reports.sales.export.print', request()->query()) }}" target="_blank" class="admin-secondary-btn">Print</a>
                <div class="topbar-chip">عدد الحجوزات المعروضة: {{ $bookings->total() }}</div>
            </div>
        </div>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-2xl border border-slate-200 px-4 py-3">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-2xl border border-slate-200 px-4 py-3">
            <div class="flex gap-3">
                <button class="admin-primary-btn">تطبيق</button>
                <a href="{{ route('admin.reports.sales') }}" class="admin-secondary-btn">إعادة</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">إجمالي المبيعات</p><p class="mt-3 text-3xl font-black">{{ number_format($totalSales, 2) }} ر.س</p></div>
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">مبيعات اليوم</p><p class="mt-3 text-3xl font-black">{{ number_format($dailySales, 2) }} ر.س</p></div>
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">متوسط الطلب</p><p class="mt-3 text-3xl font-black">{{ number_format($averageOrder, 2) }} ر.س</p></div>
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">مبيعات الأسبوع</p><p class="mt-3 text-3xl font-black">{{ number_format($weeklySales, 2) }} ر.س</p></div>
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">مبيعات الشهر</p><p class="mt-3 text-3xl font-black">{{ number_format($monthlySales, 2) }} ر.س</p></div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">المدفوعات الفاشلة</p><p class="mt-3 text-3xl font-black text-rose-700">{{ $failedPaymentsCount }}</p></div>
        <div class="stat-tile p-6"><p class="text-sm text-slate-500">الحجوزات الملغاة</p><p class="mt-3 text-3xl font-black text-amber-700">{{ $cancelledBookingsCount }}</p></div>
        <div class="stat-tile p-6 md:col-span-2"><p class="text-sm text-slate-500">الكوبونات الفعالة حالياً</p><p class="mt-3 text-3xl font-black text-violet-700">{{ $activeCouponsCount }}</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="admin-card admin-form-card">
            <h2 class="admin-section-title">أعلى الفعاليات إيراداً</h2>
            <div class="mt-6 space-y-4">
                @forelse($topEvents as $row)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold">{{ $row->event?->title ?? 'فعالية محذوفة' }}</p>
                                <p class="mt-1 text-sm text-slate-500">عدد الحجوزات: {{ $row->bookings_count }}</p>
                            </div>
                            <p class="text-lg font-black">{{ number_format($row->revenue, 2) }} ر.س</p>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات مبيعات بعد.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="admin-section-title">الإيرادات حسب المدينة</h2>
            <div class="mt-6 space-y-4">
                @forelse($citySales as $row)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-bold">{{ $row->city_name }}</p>
                            <p class="text-lg font-black">{{ number_format($row->revenue, 2) }} ر.س</p>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات للمدن حتى الآن.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="admin-card admin-form-card">
            <h2 class="admin-section-title">الأقل أداءً</h2>
            <div class="mt-6 space-y-4">
                @forelse($lowPerformingEvents as $row)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold">{{ $row->event?->title ?? 'فعالية محذوفة' }}</p>
                                <p class="mt-1 text-sm text-slate-500">عدد الحجوزات: {{ $row->bookings_count }}</p>
                            </div>
                            <p class="text-lg font-black">{{ number_format($row->revenue, 2) }} ر.س</p>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات كافية لقياس الأداء المنخفض.</div>
                @endforelse
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="admin-section-title">الأداء حسب بوابة الدفع</h2>
            <div class="mt-6 space-y-4">
                @forelse($gatewaySales as $row)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold">{{ strtoupper($row->gateway) }}</p>
                                <p class="mt-1 text-sm text-slate-500">عدد العمليات: {{ $row->operations }}</p>
                            </div>
                            <p class="text-lg font-black">{{ number_format($row->total, 2) }} ر.س</p>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty">لا توجد بيانات لبوابات الدفع حالياً.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="admin-card admin-form-card">
        <h2 class="admin-section-title">استخدام الكوبونات والخصومات</h2>
        <div class="mt-6 space-y-4">
            @forelse($couponUsage as $row)
                <div class="rounded-[1.5rem] border border-slate-100 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-bold">{{ $row->coupon?->code ?? 'كوبون محذوف' }}</p>
                            <p class="mt-1 text-sm text-slate-500">عدد الاستخدامات: {{ $row->uses_count }}</p>
                        </div>
                        <p class="text-lg font-black text-violet-700">{{ number_format((float) $row->total_discount, 2) }} ر.س</p>
                    </div>
                </div>
            @empty
                <div class="admin-empty">لا توجد كوبونات مستخدمة ضمن الفترة الحالية.</div>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
            <div class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">{{ $booking->user->name }} • {{ $booking->event->city?->name }}</p>
                        <h2 class="mt-2 text-xl font-black">{{ $booking->event->title }}</h2>
                    </div>
                    <div class="text-left">
                        <p class="font-black">{{ number_format($booking->total_amount, 2) }} ر.س</p>
                        <p class="text-sm text-slate-500">{{ strtoupper($booking->latestPayment?->gateway ?? 'N/A') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="admin-empty">لا توجد طلبات مدفوعة ضمن الفلترة الحالية.</div>
        @endforelse
    </div>

    <div>{{ $bookings->links() }}</div>
</section>
@endsection
