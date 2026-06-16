@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الكوبونات';
    $pageDescription = 'أكواد الخصم، حالتها، وقيمتها داخل نظام الحجوزات.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="admin-page-kicker">العروض والخصومات</span>
            <h1 class="admin-page-title">إدارة الكوبونات</h1>
            <p class="admin-page-description">أنشئ كوبونات خصم وتابع حالتها وقيمتها من مكان واحد.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="admin-primary-btn">إضافة كوبون</a>
    </div>
    <div class="space-y-4">
        @foreach($coupons as $coupon)
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="admin-card interactive-card block p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">{{ $coupon->type }}</p>
                        <h2 class="mt-2 text-2xl font-black">{{ $coupon->code }}</h2>
                    </div>
                    <div class="text-left">
                        <p class="font-black">{{ $coupon->value }}</p>
                        <p class="text-sm {{ $coupon->is_active ? 'text-emerald-600' : 'text-rose-600' }}">{{ $coupon->is_active ? 'نشط' : 'متوقف' }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    <div>{{ $coupons->links() }}</div>
</section>
@endsection
