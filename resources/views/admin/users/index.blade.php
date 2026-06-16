@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المستخدمين';
    $pageDescription = 'التحكم الكامل في المستخدمين الداخليين والعملاء، مع تفعيل الحساب وتحديد الدور.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">الصلاحيات والحسابات</span>
                <h2 class="admin-page-title">مركز إدارة المستخدمين</h2>
                <p class="admin-page-description">تحكم في الموظفين والعملاء والمنظمين من شاشة واحدة مع إمكانية التفعيل والتصفية السريعة.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="admin-primary-btn">إضافة مستخدم</a>
        </div>
    </div>

    <div class="admin-filter-panel">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو البريد أو الجوال" class="rounded-2xl border border-slate-200 px-4 py-3 md:col-span-2">
            <select name="role" class="rounded-2xl border border-slate-200 px-4 py-3">
                <option value="">كل الأدوار</option>
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-3">
                <button class="admin-primary-btn flex-1">فلترة</button>
                <a href="{{ route('admin.users.index') }}" class="admin-secondary-btn">إعادة</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($users as $user)
            <article class="admin-card interactive-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <span class="badge-pill badge-pill-muted">{{ $roles[$user->role] ?? $user->role }}</span>
                            <span class="badge-pill {{ $user->is_active ? 'badge-pill-success' : 'badge-pill-danger' }}">{{ $user->is_active ? 'نشط' : 'معطل' }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black">{{ $user->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $user->email }} • {{ $user->phone ?: 'بدون جوال' }}</p>
                        </div>
                        <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                            <p>الحجوزات: <span class="font-black text-slate-900">{{ $user->bookings_count }}</span></p>
                            <p>فعالياته: <span class="font-black text-slate-900">{{ $user->organized_events_count }}</span></p>
                            <p>آخر تحديث: <span class="font-black text-slate-900">{{ $user->updated_at?->diffForHumans() }}</span></p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="admin-secondary-btn">تعديل</a>
                </div>
            </article>
        @empty
            <div class="admin-empty xl:col-span-2">لا توجد حسابات حالياً.</div>
        @endforelse
    </div>

    <div>{{ $users->links() }}</div>
</section>
@endsection
