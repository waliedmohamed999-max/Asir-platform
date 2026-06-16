@extends('layouts.admin')

@php
    $pageTitle = $venue->exists ? 'تعديل الموقع' : 'إضافة موقع جديد';
    $pageDescription = 'مكان أو قاعة أو شاطئ مع المدينة والعنوان والخرائط والسعة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر المواقع</span>
                <h2 class="admin-page-title">{{ $venue->exists ? 'تعديل الموقع' : 'إضافة موقع جديد' }}</h2>
                <p class="admin-page-description">أضف القاعة أو الشاطئ أو الوجهة مع المدينة والعنوان والسعة وروابط الخرائط.</p>
            </div>
            <a href="{{ route('admin.venues.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $venue->exists ? route('admin.venues.update', $venue) : route('admin.venues.store') }}" class="admin-form space-y-6">
        @csrf
        @if($venue->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <input name="name" value="{{ old('name', $venue->name) }}" placeholder="اسم الموقع" class="w-full rounded-2xl border-slate-200 md:col-span-2">
                <input name="slug" value="{{ old('slug', $venue->slug) }}" placeholder="Slug اختياري" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <select name="city_id" class="w-full rounded-2xl border-slate-200">
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected(old('city_id', $venue->city_id) == $city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="capacity" value="{{ old('capacity', $venue->capacity) }}" placeholder="السعة" class="w-full rounded-2xl border-slate-200">
                <input type="number" name="sort_order" value="{{ old('sort_order', $venue->sort_order) }}" placeholder="الترتيب" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 space-y-4">
                <input name="address" value="{{ old('address', $venue->address) }}" placeholder="العنوان" class="w-full rounded-2xl border-slate-200">
                <input name="google_maps_url" value="{{ old('google_maps_url', $venue->google_maps_url) }}" placeholder="رابط خرائط Google" class="w-full rounded-2xl border-slate-200">
                <label class="media-uploader" data-media-upload data-preview-target="venue-image-preview" data-placeholder="اسحب صورة الموقع هنا أو اضغط للاختيار">
                    <input type="file" name="image_file" accept="image/*" class="sr-only">
                    <div class="space-y-2 text-center">
                        <p class="text-sm font-black text-slate-800">اسحب صورة الموقع هنا أو اضغط للاختيار</p>
                        <p class="text-xs text-slate-500" data-media-text>صورة القاعة أو الوجهة</p>
                    </div>
                </label>
                <div id="venue-image-preview" class="media-preview-grid"></div>
                <input name="image_url" value="{{ old('image_url', $venue->image_url) }}" placeholder="أو رابط الصورة" class="w-full rounded-2xl border-slate-200">
                @if($venue->image_url)
                    <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                        <img src="{{ $venue->image_url }}" alt="Venue image" class="h-44 w-full object-cover">
                        <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                            <input type="checkbox" name="remove_image" value="1">
                            حذف الصورة الحالية
                        </label>
                    </div>
                @endif
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input name="latitude" value="{{ old('latitude', $venue->latitude) }}" placeholder="Latitude" class="w-full rounded-2xl border-slate-200">
                <input name="longitude" value="{{ old('longitude', $venue->longitude) }}" placeholder="Longitude" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <textarea name="description" rows="5" placeholder="الوصف" class="w-full rounded-2xl border-slate-200">{{ old('description', $venue->description) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $venue->is_active ?? true))> موقع نشط</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">سيظهر الموقع داخل الفعاليات المرتبطة بعد الحفظ.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.venues.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ الموقع</button>
            </div>
        </div>
    </form>
</section>
@endsection
