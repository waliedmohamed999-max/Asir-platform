@extends('layouts.admin')

@php
    $pageTitle = 'تفاصيل الحجز';
    $pageDescription = 'عرض الطلب، التذاكر، الدفع، وتحديث الحالة أو إعادة إرسال التذاكر.';
    $ticketQuantity = $booking->items->sum('quantity');
    $paidAmount = $booking->payment->where('status', 'paid')->sum('amount');
@endphp

@push('styles')
    <style>
        .booking-detail-hero {
            background:
                radial-gradient(circle at 12% 18%, rgba(16, 185, 129, .12), transparent 24rem),
                radial-gradient(circle at 90% 10%, rgba(124, 58, 237, .12), transparent 22rem),
                linear-gradient(135deg, rgba(255,255,255,.98), rgba(248,250,252,.96));
        }
        .ticket-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(124, 58, 237, .14);
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--ticket-color, #7c3aed) 12%, transparent), transparent 22rem),
                linear-gradient(180deg, #fff, #fbfcff);
            box-shadow: 0 18px 42px rgba(15,23,42,.06);
        }
        .ticket-card::before {
            content: '';
            position: absolute;
            inset-block: 24px;
            inset-inline-start: 0;
            width: 5px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--ticket-color, #7c3aed), #e8356d);
        }
        .ticket-card::after {
            content: '';
            position: absolute;
            inset-block: 22px;
            inset-inline-end: 154px;
            width: 1px;
            border-inline-start: 1px dashed rgba(148, 163, 184, .6);
        }
        .ticket-qr-stub {
            border-radius: 24px;
            background: linear-gradient(145deg, #0f172a, color-mix(in srgb, var(--ticket-color, #7c3aed) 42%, #0f172a));
            color: #fff;
        }
        .ticket-code {
            font-family: 'Outfit', 'Cairo', sans-serif;
            direction: ltr;
            text-align: left;
        }
        .booking-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #eef2f7;
            padding: 10px 0;
        }
        .booking-summary-row:last-child { border-bottom: 0; }
    </style>
@endpush

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head booking-detail-hero">
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-stretch">
            <div class="flex flex-col justify-between gap-5">
                <div>
                    <span class="admin-page-kicker">{{ $booking->reference }}</span>
                    <h2 class="admin-page-title">{{ $booking->event?->title ?? 'فعالية غير متاحة' }}</h2>
                    <p class="admin-page-description">{{ $booking->user?->name ?? 'عميل غير مسجل' }} • {{ $booking->customer_email }} • {{ $booking->customer_phone }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="window.print()" class="admin-secondary-btn">طباعة الحجز</button>
                    <form method="POST" action="{{ route('admin.bookings.resend', $booking) }}">
                        @csrf
                        <button class="admin-secondary-btn">إعادة إرسال التذكرة</button>
                    </form>
                    <a href="{{ route('admin.bookings.index') }}" class="admin-primary-btn">رجوع</a>
                </div>
            </div>

            <div class="rounded-[1.6rem] bg-slate-950 p-5 text-white">
                <p class="text-sm font-bold text-white/65">ملخص الحجز</p>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-xs text-white/60">التذاكر</p>
                        <p class="mt-2 text-3xl font-black">{{ $ticketQuantity }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-xs text-white/60">المدفوع</p>
                        <p class="mt-2 text-3xl font-black">{{ number_format($paidAmount, 0) }}</p>
                    </div>
                </div>
                <div class="mt-3 rounded-2xl bg-white/10 p-4">
                    <p class="text-xs text-white/60">الإجمالي النهائي</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format($booking->total_amount, 2) }} ر.س</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <div class="space-y-6">
            <div class="admin-card admin-form-card">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="admin-section-title">التذاكر الصادرة</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">كل تذكرة مع كود QR الخاص بها وحالة بياناتها.</p>
                    </div>
                    <span class="topbar-chip">{{ $ticketQuantity }} تذكرة</span>
                </div>
                <div class="mt-6 grid gap-4">
                    @foreach($booking->items as $item)
                        @php
                            $ticketColor = $item->ticket?->label_color ?: '#7c3aed';
                            $features = collect($item->ticket?->features ?? [])->filter()->take(3);
                        @endphp
                        <div class="ticket-card p-4 md:p-5" style="--ticket-color: {{ $ticketColor }}">
                            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_150px] lg:items-stretch">
                                <div class="relative z-10 min-w-0">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded-full px-3 py-1 text-xs font-black text-white" style="background: {{ $ticketColor }}">{{ $item->ticket_name }}</span>
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">x{{ $item->quantity }}</span>
                                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">صالحة للدخول</span>
                                            </div>
                                            <h4 class="mt-4 text-xl font-black text-slate-950">{{ $booking->event?->title ?? 'فعالية غير متاحة' }}</h4>
                                            <p class="mt-2 text-sm font-bold text-slate-500">
                                                {{ $booking->event?->city?->name ?? 'مدينة غير محددة' }} • {{ $item->attendee_date?->translatedFormat('d M Y') ?? $booking->booking_date?->translatedFormat('d M Y') ?? 'تاريخ غير محدد' }}
                                            </p>
                                        </div>
                                        <div class="rounded-2xl bg-slate-950 px-4 py-3 text-left text-white">
                                            <p class="text-xs text-white/55">الإجمالي</p>
                                            <p class="mt-1 text-lg font-black">{{ number_format($item->line_total, 2) }} ر.س</p>
                                        </div>
                                    </div>

                                    @if($item->ticket?->description)
                                        <p class="mt-4 max-w-3xl text-sm font-medium leading-7 text-slate-600">{{ $item->ticket->description }}</p>
                                    @endif

                                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-bold text-slate-400">سعر الوحدة</p>
                                            <p class="mt-1 font-black text-slate-950">{{ number_format($item->unit_price, 2) }} ر.س</p>
                                        </div>
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-bold text-slate-400">نوع التذكرة</p>
                                            <p class="mt-1 font-black text-slate-950">{{ $item->ticket?->type ?? 'regular' }}</p>
                                        </div>
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-bold text-slate-400">حالة التذكرة</p>
                                            <p class="mt-1 font-black text-slate-950">{{ $item->ticket?->status ?? 'active' }}</p>
                                        </div>
                                    </div>

                                    @if($features->isNotEmpty())
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach($features as $feature)
                                                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700">{{ $feature }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Ticket Code</p>
                                            <p class="text-xs font-bold text-slate-400">مرسل للتطبيق والعميل</p>
                                        </div>
                                        <p class="ticket-code mt-2 break-all text-sm font-black text-slate-800">{{ $item->qr_token }}</p>
                                    </div>
                                </div>

                                <div class="ticket-qr-stub relative z-10 flex flex-col items-center justify-between gap-4 p-4 text-center">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/55">Aseer Pass</p>
                                        <p class="mt-1 text-sm font-black">منصة عسير</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3 shadow-xl">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($item->qr_token) }}" alt="QR Code" class="h-28 w-28 rounded-xl">
                                    </div>
                                    <p class="text-xs font-bold leading-5 text-white/65">امسح الكود للتحقق من صلاحية التذكرة عند الدخول</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="admin-card admin-form-card">
                <h3 class="admin-section-title">سجل المدفوعات</h3>
                <div class="mt-6 space-y-4">
                    @forelse($booking->payment as $payment)
                        <div class="rounded-[1.5rem] border border-slate-100 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="font-bold">{{ strtoupper($payment->gateway) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $payment->transaction_reference }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $payment->paid_at?->translatedFormat('d M Y - h:i A') ?? 'غير مدفوع بعد' }}</p>
                                </div>
                                <div class="text-left">
                                    <p class="text-lg font-black">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</p>
                                    <p class="text-sm {{ $payment->status === 'paid' ? 'text-emerald-600' : ($payment->status === 'failed' ? 'text-rose-600' : 'text-amber-600') }}">{{ $payment->status }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty">لا توجد مدفوعات مسجلة لهذا الحجز.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card admin-form-card">
                <h3 class="admin-section-title">تحديث الحالة</h3>
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-2 block text-sm font-bold">حالة الحجز</label>
                        <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                            @foreach($bookingStatuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $booking->status) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">حالة الدفع</label>
                        <select name="payment_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                            @foreach($paymentStatuses as $status)
                                <option value="{{ $status }}" @selected(old('payment_status', $booking->payment_status) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="admin-primary-btn w-full">حفظ الحالة</button>
                </form>
            </div>

            <div class="admin-card admin-form-card">
                <h3 class="admin-section-title">ملخص الطلب</h3>
                <div class="mt-5 text-sm">
                    <div class="booking-summary-row"><span class="text-slate-500">العميل</span><span class="font-bold text-slate-900">{{ $booking->user?->name }}</span></div>
                    <div class="booking-summary-row"><span class="text-slate-500">الفعالية</span><span class="font-bold text-slate-900">{{ $booking->event?->title }}</span></div>
                    <div class="booking-summary-row"><span class="text-slate-500">المدينة</span><span class="font-bold text-slate-900">{{ $booking->event?->city?->name ?? '—' }}</span></div>
                    <div class="booking-summary-row"><span class="text-slate-500">التصنيف</span><span class="font-bold text-slate-900">{{ $booking->event?->category?->name ?? '—' }}</span></div>
                    <div class="booking-summary-row"><span class="text-slate-500">التاريخ</span><span class="font-bold text-slate-900">{{ $booking->booking_date?->translatedFormat('d M Y - h:i A') }}</span></div>
                    <div class="booking-summary-row"><span class="text-slate-500">الكوبون</span><span class="font-bold text-slate-900">{{ $booking->coupon?->code ?? 'لا يوجد' }}</span></div>
                </div>

                <div class="mt-6 rounded-[1.5rem] bg-slate-900 p-5 text-white">
                    <div class="flex justify-between text-sm"><span>قبل الخصم</span><span>{{ number_format($booking->subtotal_amount, 2) }} ر.س</span></div>
                    <div class="mt-2 flex justify-between text-sm text-slate-300"><span>الخصم</span><span>{{ number_format($booking->discount_amount, 2) }} ر.س</span></div>
                    <div class="mt-3 flex justify-between border-t border-white/10 pt-3 text-lg font-black"><span>الإجمالي</span><span>{{ number_format($booking->total_amount, 2) }} ر.س</span></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
