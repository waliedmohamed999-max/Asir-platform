@extends('layouts.admin')

@php
    $pageTitle = 'محادثات التطبيق';
    $pageDescription = 'استقبل طلبات المستخدمين من صفحة المحادثات داخل التطبيق ورد عليها مباشرة.';
    $statusLabels = ['open' => 'مفتوحة', 'pending' => 'بانتظار العميل', 'closed' => 'مغلقة'];
    $statusClasses = ['open' => 'badge-pill-danger', 'pending' => 'badge-pill-warning', 'closed' => 'badge-pill-success'];
@endphp

@section('content')
    <section class="admin-card admin-page-head mb-5">
        <span class="admin-page-kicker">مركز الدعم</span>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="admin-page-title">محادثات المستخدمين من التطبيق</h2>
                <p class="admin-page-description">كل محادثة تبدأ من شاشة الترحيب داخل التطبيق، ثم تصل هنا للمسؤول حتى يرد ويتابع الحالة.</p>
            </div>
        </div>
    </section>

    <section class="mb-5 grid gap-3 md:grid-cols-4">
        @foreach([
            ['label' => 'الإجمالي', 'value' => $stats['total'], 'class' => 'text-violet-700'],
            ['label' => 'مفتوحة', 'value' => $stats['open'], 'class' => 'text-rose-600'],
            ['label' => 'بانتظار العميل', 'value' => $stats['pending'], 'class' => 'text-amber-600'],
            ['label' => 'مغلقة', 'value' => $stats['closed'], 'class' => 'text-emerald-600'],
        ] as $stat)
            <div class="stat-tile p-5">
                <p class="text-sm font-black text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-black {{ $stat['class'] }}">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </section>

    <form class="admin-filter-panel mb-5 grid gap-3 lg:grid-cols-[1fr_220px_auto]" method="GET">
        <input name="search" value="{{ $search }}" placeholder="ابحث بالاسم، اليوزر، البريد أو الجوال">
        <select name="status">
            <option value="">كل الحالات</option>
            @foreach($statusLabels as $key => $label)
                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="admin-primary-btn">تصفية</button>
    </form>

    <section class="admin-card p-4">
        @forelse($conversations as $conversation)
            <a href="{{ route('admin.support-conversations.show', $conversation) }}" class="mb-3 flex flex-col gap-3 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-violet-200 hover:shadow-lg lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge-pill {{ $statusClasses[$conversation->status] ?? 'badge-pill-muted' }}">{{ $statusLabels[$conversation->status] ?? $conversation->status }}</span>
                        <span class="badge-pill badge-pill-info">{{ $conversation->messages_count }} رسالة</span>
                        <span class="badge-pill badge-pill-muted">{{ $conversation->topic }}</span>
                    </div>
                    <h3 class="mt-3 text-lg font-black text-slate-950">{{ $conversation->customer_name }} <span class="text-sm text-slate-400">/ {{ '@'.$conversation->username }}</span></h3>
                    <p class="mt-1 truncate text-sm text-slate-500">{{ $conversation->bio ?: 'بدون وصف' }}</p>
                </div>
                <div class="text-sm font-bold text-slate-500 lg:text-left">
                    <p>{{ $conversation->customer_email ?: 'لا يوجد بريد' }}</p>
                    <p class="mt-1">{{ $conversation->last_message_at?->diffForHumans() ?: $conversation->created_at->diffForHumans() }}</p>
                </div>
            </a>
        @empty
            <div class="admin-empty">لا توجد محادثات حتى الآن.</div>
        @endforelse

        <div class="mt-4">{{ $conversations->links() }}</div>
    </section>
@endsection
