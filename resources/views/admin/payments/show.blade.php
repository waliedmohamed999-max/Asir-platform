@extends('layouts.admin')

@php
    $pageTitle = 'تفاصيل عملية الدفع';
    $pageDescription = 'مرجع العملية، الحجز المرتبط، الحمولة الخام، وتحديث الحالة يدوياً.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">تفاصيل الدفع</span>
                <h2 class="admin-page-title">تفاصيل عملية الدفع</h2>
                <p class="admin-page-description">مرجع العملية، الطلب المرتبط، وحالة المعاملة الحالية.</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="admin-primary-btn">رجوع</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_.9fr]">
        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">معلومات العملية</h2>
            <div class="mt-6 space-y-3 text-sm">
                <div class="flex items-center justify-between"><span class="text-slate-500">رقم العملية</span><span class="font-bold">{{ $payment->transaction_reference }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">بوابة الدفع</span><span class="font-bold">{{ strtoupper($payment->gateway) }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">المبلغ</span><span class="font-bold">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">الحالة</span><span class="badge-pill {{ $payment->status === 'paid' ? 'badge-pill-success' : ($payment->status === 'failed' ? 'badge-pill-danger' : ($payment->status === 'refunded' ? 'badge-pill-warning' : 'badge-pill-muted')) }}">{{ $payment->status }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">العميل</span><span class="font-bold">{{ $payment->booking?->user?->name }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">الحجز</span><span class="font-bold">{{ $payment->booking?->reference }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">الفعالية</span><span class="font-bold">{{ $payment->booking?->event?->title }}</span></div>
                <div class="flex items-center justify-between"><span class="text-slate-500">الوقت</span><span class="font-bold">{{ $payment->paid_at?->translatedFormat('d M Y - h:i A') ?? 'غير مسجل' }}</span></div>
            </div>

            <div class="mt-6 rounded-[1.5rem] bg-slate-50 p-4">
                <p class="mb-3 text-sm font-black">سجل الحمولة / Payload</p>
                <pre class="overflow-x-auto text-xs leading-6 text-slate-700">{{ json_encode($payment->payload ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card admin-form-card">
                <h2 class="text-2xl font-black">تحديث الحالة</h2>
                <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $payment->status) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button class="admin-primary-btn w-full">حفظ الحالة</button>
                </form>
            </div>

            <div class="admin-card admin-form-card">
                <h2 class="text-2xl font-black">عناصر الحجز المرتبط</h2>
                <div class="mt-6 space-y-3">
                    @forelse($payment->booking?->items ?? [] as $item)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <p class="font-bold">{{ $item->ticket_name }}</p>
                            <p class="mt-1 text-sm text-slate-500">الكمية: {{ $item->quantity }} • {{ number_format($item->line_total, 2) }} ر.س</p>
                        </div>
                    @empty
                        <div class="admin-empty">لا توجد عناصر مرتبطة.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
