@extends('layouts.admin')

@php
    $pageTitle = $city->exists ? 'تعديل المدينة' : 'إضافة مدينة جديدة';
    $pageDescription = 'بيانات بسيطة وآمنة للمدينة مع ترتيب وتفعيل.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر المدن</span>
                <h2 class="admin-page-title">{{ $city->exists ? 'تعديل المدينة' : 'إضافة مدينة جديدة' }}</h2>
                <p class="admin-page-description">بيانات بسيطة وآمنة للمدينة مع slug وترتيب وحالة تفعيل.</p>
            </div>
            <a href="{{ route('admin.cities.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" action="{{ $city->exists ? route('admin.cities.update', $city) : route('admin.cities.store') }}" class="admin-form space-y-6">
        @csrf
        @if($city->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <input name="name" value="{{ old('name', $city->name) }}" placeholder="اسم المدينة" class="w-full rounded-2xl border-slate-200 md:col-span-2">
                <input name="slug" value="{{ old('slug', $city->slug) }}" placeholder="Slug اختياري" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <input type="number" name="sort_order" value="{{ old('sort_order', $city->sort_order) }}" placeholder="الترتيب" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $city->is_active ?? true))> مدينة نشطة</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">سيتم تحديث ظهور المدينة في التطبيق والمنصة.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.cities.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ المدينة</button>
            </div>
        </div>
    </form>
</section>
@endsection
