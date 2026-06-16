@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المدفوعات';
    $pageDescription = 'مراجعة عمليات الدفع، تتبع حالة كل معاملة، والتحقق اليدوي عند الحاجة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">التحصيل المالي</span>
                <h2 class="admin-page-title">سجل المدفوعات</h2>
                <p class="admin-page-description">تابع البوابات، راقب العمليات الفاشلة أو المسترجعة، وادخل إلى كل معاملة بشكل مباشر.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.payments.export.csv', request()->query()) }}" class="admin-secondary-btn">CSV</a>
                <a href="{{ route('admin.payments.export.xlsx', request()->query()) }}" class="admin-success-btn">XLSX</a>
                <a href="{{ route('admin.payments.export.pdf', request()->query()) }}" target="_blank" class="admin-danger-btn !bg-rose-50 !text-rose-700 !border !border-rose-200 !shadow-none">PDF</a>
                <a href="{{ route('admin.payments.export.print', request()->query()) }}" target="_blank" class="admin-secondary-btn">Print</a>
                <div class="topbar-chip">إجمالي العمليات: {{ $payments->total() }}</div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">كل العمليات</p><p class="mt-3 text-4xl font-black">{{ $stats['total'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">مدفوعة</p><p class="mt-3 text-4xl font-black text-emerald-700">{{ $stats['paid'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">فاشلة</p><p class="mt-3 text-4xl font-black text-rose-700">{{ $stats['failed'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">مسترجعة</p><p class="mt-3 text-4xl font-black text-amber-700">{{ $stats['refunded'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">القيمة المحصلة</p><p class="mt-3 text-4xl font-black">{{ number_format($stats['amount'], 2) }} <span class="text-2xl">ر.س</span></p></div>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="مرجع العملية / الحجز / العميل" class="rounded-2xl border border-slate-200 px-4 py-3 md:col-span-2">
            <select name="status" class="rounded-2xl border border-slate-200 px-4 py-3">
                <option value="">كل الحالات</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select name="gateway" class="rounded-2xl border border-slate-200 px-4 py-3">
                <option value="">كل البوابات</option>
                @foreach($gateways as $gateway)
                    <option value="{{ $gateway }}" @selected(request('gateway') === $gateway)>{{ strtoupper($gateway) }}</option>
                @endforeach
            </select>
            <div class="flex gap-3 md:col-span-4">
                <button class="admin-primary-btn">فلترة</button>
                <a href="{{ route('admin.payments.index') }}" class="admin-secondary-btn">إعادة</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($payments as $payment)
            <article class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <span class="badge-pill badge-pill-muted">{{ strtoupper($payment->gateway) }}</span>
                            <span class="badge-pill {{ $payment->status === 'paid' ? 'badge-pill-success' : ($payment->status === 'failed' ? 'badge-pill-danger' : ($payment->status === 'refunded' ? 'badge-pill-warning' : 'badge-pill-muted')) }}">{{ $payment->status }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black">{{ $payment->transaction_reference }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $payment->booking?->reference }} • {{ $payment->booking?->user?->name }} • {{ $payment->booking?->event?->title }}</p>
                        </div>
                        <p class="text-sm text-slate-600">التاريخ: {{ $payment->paid_at?->translatedFormat('d M Y - h:i A') ?? 'غير مسجل' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="text-lg font-black">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</p>
                        <a href="{{ route('admin.payments.show', $payment) }}" class="admin-secondary-btn">التفاصيل</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-empty">لا توجد عمليات دفع مطابقة.</div>
        @endforelse
    </div>

    <div>{{ $payments->links() }}</div>
</section>
@endsection
