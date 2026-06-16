@extends('layouts.app')

@section('content')
<section class="section-shell py-10">
    @php
        $paymentBadges = [
            'paid' => 'bg-emerald-100 text-emerald-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'failed' => 'bg-rose-100 text-rose-700',
            'refunded' => 'bg-slate-200 text-slate-700',
        ];
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
        <div class="soft-card overflow-hidden p-0">
            <div class="grid gap-0 lg:grid-cols-[1.1fr_.9fr]">
                <div class="bg-[linear-gradient(135deg,#0f172a_0%,#14213d_45%,#164e63_100%)] p-8 text-white lg:p-10">
                    <p class="text-sm font-bold text-cyan-200">مرحباً {{ $user->name }}</p>
                    <h1 class="mt-3 text-3xl font-black lg:text-[2.4rem]">لوحة المستخدم</h1>
                    <p class="mt-4 max-w-2xl text-[14px] leading-7 text-slate-200 lg:text-[15px]">
                        تابع حجوزاتك، راقب حالة الدفع، واستعرض الفعاليات القادمة من مكان واحد بتصميم أوضح وتجربة أسرع.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ route('bookings.index') }}" class="rounded-full bg-white px-5 py-3 text-sm font-black text-slate-900">عرض الحجوزات</a>
                        <a href="{{ route('home') }}" class="rounded-full border border-white/25 px-5 py-3 text-sm font-bold text-white/90">استكشاف الفعاليات</a>
                    </div>
                </div>

                <div class="grid gap-4 bg-slate-50 p-6 lg:p-8">
                    <div class="rounded-[1.75rem] bg-white p-5 shadow-sm ring-1 ring-slate-100">
                        <p class="text-sm text-slate-500">إجمالي الحجوزات</p>
                        <p class="mt-3 text-3xl font-black text-slate-900">{{ $user->bookings_count }}</p>
                        <p class="mt-2 text-sm text-slate-500">إجمالي إنفاقك الحالي {{ number_format($stats['spent_total'], 2) }} ر.س</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            <p class="text-sm text-slate-500">التذاكر المحجوزة</p>
                            <p class="mt-3 text-xl font-black text-cyan-700">{{ $stats['tickets_booked'] }}</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm ring-1 ring-slate-100">
                            <p class="text-sm text-slate-500">الفعاليات القادمة</p>
                            <p class="mt-3 text-xl font-black text-violet-700">{{ $stats['upcoming_events'] }}</p>
                        </div>
                    </div>
                    <div class="rounded-[1.5rem] border border-dashed border-cyan-200 bg-cyan-50/70 p-5">
                        <p class="text-sm text-slate-500">المدينة الأكثر حضوراً</p>
                        <p class="mt-2 text-xl font-black text-slate-900">{{ $stats['favorite_city'] ?: 'لا توجد بيانات كافية بعد' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1">
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">حجوزات مدفوعة</p>
                <p class="mt-3 text-2xl font-black text-emerald-700">{{ $stats['paid_bookings'] }}</p>
            </div>
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">بانتظار المراجعة</p>
                <p class="mt-3 text-2xl font-black text-amber-600">{{ $stats['pending_bookings'] }}</p>
            </div>
            <div class="soft-card p-6">
                <p class="text-sm text-slate-500">حجوزات مسترجعة</p>
                <p class="mt-3 text-2xl font-black text-slate-700">{{ $stats['refunded_bookings'] }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <div class="soft-card p-6 lg:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black">آخر الحجوزات</h2>
                    <p class="mt-1 text-sm text-slate-500">نظرة سريعة على أحدث الطلبات وحالاتها.</p>
                </div>
                <a href="{{ route('bookings.index') }}" class="rounded-full border border-cyan-100 px-4 py-2 text-sm font-bold text-cyan-700">عرض الكل</a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($recentBookings as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="flex flex-wrap items-center justify-between gap-4 rounded-[1.6rem] border border-slate-100 p-4 transition hover:border-cyan-100 hover:bg-cyan-50/30">
                        <div class="min-w-0">
                            <p class="font-black text-slate-900">{{ $booking->event->title }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $booking->reference }} • {{ $booking->event->city?->name ?? 'بدون مدينة' }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ $booking->booking_date?->translatedFormat('d M Y - h:i A') }}</p>
                        </div>
                        <div class="text-left">
                            <p class="font-black text-slate-900">{{ number_format($booking->total_amount, 2) }} ر.س</p>
                            <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $paymentBadges[$booking->payment_status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $booking->payment_status }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-200 p-8 text-center text-slate-500">لا توجد حجوزات بعد. ابدأ باكتشاف الفعاليات المناسبة لك.</div>
                @endforelse
            </div>
        </div>

        <div class="soft-card p-6 lg:p-7">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black">آخر عمليات الدفع</h2>
                    <p class="mt-1 text-sm text-slate-500">حالة المدفوعات المرتبطة بطلباتك الأخيرة.</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse($recentPayments as $payment)
                    <div class="rounded-[1.5rem] border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-black text-slate-900">{{ $payment->booking?->event?->title ?? 'طلب بدون فعالية' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $payment->gateway ?: 'بوابة غير محددة' }} • {{ $payment->transaction_reference ?: 'بدون مرجع' }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $paymentBadges[$payment->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $payment->status }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3 text-sm text-slate-500">
                            <span>{{ $payment->paid_at?->translatedFormat('d M Y') ?? $payment->created_at?->translatedFormat('d M Y') }}</span>
                            <span class="font-black text-slate-900">{{ number_format($payment->amount, 2) }} {{ $payment->currency ?: 'SAR' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-200 p-8 text-center text-slate-500">لا توجد عمليات دفع معروضة حالياً.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
