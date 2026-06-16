@extends('layouts.app')

@section('title','الدفع')

@section('content')
<div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
    <div class="space-y-6">
        <div class="card p-6">
            <h2 class="text-2xl font-semibold">تفاصيل الحجز</h2>
            <p class="mt-4 text-slate-600">فعالية: {{ $booking->event->title }}</p>
            <p class="text-slate-600">المبلغ قبل الخصم: {{ number_format($booking->total_amount) }} ر.س</p>
            <p class="text-slate-600">الخصم: {{ number_format($booking->discount_amount) }} ر.س</p>
            <p class="mt-4 text-xl font-semibold">المبلغ النهائي: {{ number_format($booking->total_amount - $booking->discount_amount) }} ر.س</p>
        </div>
        <div class="card p-6">
            <h2 class="text-2xl font-semibold">طرق الدفع</h2>
            <form method="POST" action="{{ route('checkout.pay', $booking) }}" class="space-y-4 mt-5">
                @csrf
                <label class="flex items-center gap-3 rounded-3xl border border-slate-200 p-4">
                    <input type="radio" name="payment_method" value="stripe" checked />
                    <span>Stripe</span>
                </label>
                <label class="flex items-center gap-3 rounded-3xl border border-slate-200 p-4">
                    <input type="radio" name="payment_method" value="paypal" />
                    <span>PayPal</span>
                </label>
                <label class="flex items-center gap-3 rounded-3xl border border-slate-200 p-4">
                    <input type="radio" name="payment_method" value="mada" />
                    <span>مدى</span>
                </label>
                <button type="submit" class="btn-primary w-full">تأكيد الدفع</button>
            </form>
        </div>
    </div>
    <div class="rounded-[2rem] bg-brand-600 p-8 text-white shadow-xl">
        <h2 class="text-2xl font-semibold">أكمل الحجز الآن</h2>
        <p class="mt-4 text-slate-100/90">نظام الدفع الفوري والتذكرة تصل إلى بريدك بعد تأكيد الدفع مباشرةً.</p>
    </div>
</div>
@endsection
