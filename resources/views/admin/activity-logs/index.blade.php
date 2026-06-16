@extends('layouts.admin')

@php
    $pageTitle = 'سجل النشاطات';
    $pageDescription = 'مراجعة العمليات الإدارية المهمة والتغييرات الحساسة داخل لوحة التحكم.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">الأمان والمراجعة</span>
                <h2 class="admin-page-title">سجل النشاطات الإدارية</h2>
                <p class="admin-page-description">تابع كل التعديلات المهمة على الحجوزات والمدفوعات والفعاليات والإعدادات.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.activity-logs.export.csv', request()->query()) }}" class="admin-secondary-btn">CSV</a>
                <a href="{{ route('admin.activity-logs.export.xlsx', request()->query()) }}" class="admin-success-btn">XLSX</a>
                <a href="{{ route('admin.activity-logs.export.pdf', request()->query()) }}" target="_blank" class="admin-danger-btn !bg-rose-50 !text-rose-700 !border !border-rose-200 !shadow-none">PDF</a>
                <a href="{{ route('admin.activity-logs.export.print', request()->query()) }}" target="_blank" class="admin-secondary-btn">Print</a>
                <div class="topbar-chip">إجمالي السجلات: {{ $logs->total() }}</div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">إجمالي السجلات</p><p class="mt-3 text-4xl font-black">{{ $stats['total'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">سجلات اليوم</p><p class="mt-3 text-4xl font-black text-violet-700">{{ $stats['today'] }}</p></div>
        <div class="stat-tile p-5"><p class="text-sm text-slate-500">المستخدمون النشطون</p><p class="mt-3 text-4xl font-black text-cyan-700">{{ $stats['actors'] }}</p></div>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالفعل أو الوصف أو اسم المستخدم" class="xl:col-span-2">
            <select name="action">
                <option value="">كل العمليات</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <select name="user_id">
                <option value="">كل المستخدمين</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="subject_type">
                <option value="">كل الكيانات</option>
                @foreach($subjectTypes as $subjectType)
                    <option value="{{ $subjectType }}" @selected(request('subject_type') === $subjectType)>{{ class_basename($subjectType) }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}">
            <input type="date" name="date_to" value="{{ request('date_to') }}">
            <div class="flex gap-3 xl:col-span-6">
                <button class="admin-primary-btn">فلترة</button>
                <a href="{{ route('admin.activity-logs.index') }}" class="admin-secondary-btn">إعادة</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($logs as $log)
            <article class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-2">
                            <span class="badge-pill badge-pill-muted">{{ $log->action }}</span>
                            @if($log->subject_type)
                                <span class="badge-pill badge-pill-info">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                            @endif
                        </div>
                        <p class="font-black text-slate-900">{{ $log->description ?: 'بدون وصف إضافي' }}</p>
                        <p class="text-sm text-slate-500">{{ $log->user?->name ?? 'نظام' }} • {{ $log->created_at?->translatedFormat('d M Y - h:i A') }}</p>
                    </div>
                    <div class="text-left text-sm text-slate-500">
                        <p>{{ $log->ip_address ?: 'IP غير متوفر' }}</p>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-empty">لا توجد نشاطات مطابقة حالياً.</div>
        @endforelse
    </div>

    <div>{{ $logs->links() }}</div>
</section>
@endsection
