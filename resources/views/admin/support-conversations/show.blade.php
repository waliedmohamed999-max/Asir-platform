@extends('layouts.admin')

@php
    $pageTitle = 'تفاصيل المحادثة';
    $pageDescription = 'رد على المستخدم وسيصل الرد داخل تطبيق الهاتف.';
    $statusLabels = ['open' => 'مفتوحة', 'pending' => 'بانتظار العميل', 'closed' => 'مغلقة'];
@endphp

@section('content')
    <section class="grid gap-5 lg:grid-cols-[1fr_360px]">
        <div class="admin-card p-5">
            <div class="mb-5 flex flex-col gap-3 border-b border-slate-100 pb-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('admin.support-conversations.index') }}" class="text-sm font-black text-violet-700">← رجوع للمحادثات</a>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">{{ $conversation->customer_name }}</h2>
                    <p class="mt-1 text-sm font-bold text-slate-500">{{ '@'.$conversation->username }} · {{ $conversation->customer_email ?: 'بدون بريد' }}</p>
                </div>
                <span class="badge-pill badge-pill-info">{{ $statusLabels[$conversation->status] ?? $conversation->status }}</span>
            </div>

            <div class="space-y-4">
                @foreach($conversation->messages as $message)
                    @php($isAdmin = $message->sender_type === 'admin')
                    <div class="flex {{ $isAdmin ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[82%] rounded-3xl border p-4 {{ $isAdmin ? 'border-violet-100 bg-violet-50 text-slate-950' : 'border-slate-100 bg-slate-950 text-white' }}">
                            <p class="text-xs font-black {{ $isAdmin ? 'text-violet-700' : 'text-slate-300' }}">{{ $isAdmin ? ($message->sender?->name ?? 'المسؤول') : $conversation->customer_name }}</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $message->body }}</p>
                            <p class="mt-2 text-[11px] font-bold opacity-60">{{ $message->created_at->translatedFormat('d M Y - h:i A') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.support-conversations.reply', $conversation) }}" class="mt-6 rounded-3xl border border-slate-100 bg-slate-50 p-4">
                @csrf
                <label class="mb-2 block text-sm font-black text-slate-700">رد المسؤول</label>
                <textarea name="body" rows="4" required placeholder="اكتب ردك هنا..." class="w-full rounded-2xl border-slate-200">{{ old('body') }}</textarea>
                <div class="mt-3 flex justify-end">
                    <button class="admin-primary-btn">إرسال الرد للتطبيق</button>
                </div>
            </form>
        </div>

        <aside class="space-y-5">
            <div class="admin-card p-5">
                <h3 class="admin-section-title">بيانات المستخدم</h3>
                <div class="mt-5 space-y-3 text-sm">
                    <p><span class="font-black text-slate-500">الاسم:</span> {{ $conversation->customer_name }}</p>
                    <p><span class="font-black text-slate-500">اسم المستخدم:</span> {{ '@'.$conversation->username }}</p>
                    <p><span class="font-black text-slate-500">البريد:</span> {{ $conversation->customer_email ?: 'غير محدد' }}</p>
                    <p><span class="font-black text-slate-500">الجوال:</span> {{ $conversation->customer_phone ?: 'غير محدد' }}</p>
                    <p class="leading-7"><span class="font-black text-slate-500">الوصف:</span> {{ $conversation->bio ?: 'غير محدد' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.support-conversations.update', $conversation) }}" class="admin-card p-5">
                @csrf
                @method('PATCH')
                <h3 class="admin-section-title">إدارة الحالة</h3>
                <div class="mt-5 space-y-3">
                    <label class="block text-sm font-black text-slate-600">الحالة</label>
                    <select name="status">
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" @selected($conversation->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="block text-sm font-black text-slate-600">الأولوية</label>
                    <select name="priority">
                        @foreach(['low' => 'منخفضة', 'normal' => 'عادية', 'high' => 'عالية'] as $key => $label)
                            <option value="{{ $key }}" @selected($conversation->priority === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="admin-secondary-btn w-full">حفظ الحالة</button>
                </div>
            </form>
        </aside>
    </section>
@endsection
