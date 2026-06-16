@extends('layouts.admin')

@php
    $pageTitle = $organizer->exists ? 'تعديل المنظم' : 'إضافة منظم جديد';
    $pageDescription = 'بيانات المنظم، الشعار، النبذة، التواصل، وتفعيل الحساب.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر المنظمين</span>
                <h2 class="admin-page-title">{{ $organizer->exists ? 'تعديل المنظم' : 'إضافة منظم جديد' }}</h2>
                <p class="admin-page-description">أدخل بيانات المنظم، وسائل التواصل، والحالة ليظهر بشكل صحيح داخل النظام.</p>
            </div>
            <a href="{{ route('admin.organizers.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $organizer->exists ? route('admin.organizers.update', $organizer) : route('admin.organizers.store') }}" class="admin-form space-y-6">
        @csrf
        @if($organizer->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="name" value="{{ old('name', $organizer->name) }}" placeholder="اسم المنظم" class="w-full rounded-2xl border-slate-200">
                <input name="email" value="{{ old('email', $organizer->email) }}" placeholder="البريد الإلكتروني" class="w-full rounded-2xl border-slate-200">
                <input name="phone" value="{{ old('phone', $organizer->phone) }}" placeholder="الجوال" class="w-full rounded-2xl border-slate-200">
                <input name="whatsapp" value="{{ old('whatsapp', $organizer->whatsapp) }}" placeholder="واتساب" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input type="password" name="password" placeholder="{{ $organizer->exists ? 'كلمة مرور جديدة اختيارية' : 'كلمة المرور' }}" class="w-full rounded-2xl border-slate-200">
                <input type="password" name="password_confirmation" placeholder="تأكيد كلمة المرور" class="w-full rounded-2xl border-slate-200">
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">التواصل والنبذة</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="space-y-3">
                    <label class="media-uploader" data-media-upload data-preview-target="organizer-logo-preview" data-placeholder="اسحب شعار المنظم هنا أو اضغط للاختيار">
                        <input type="file" name="logo_file" accept="image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب شعار المنظم هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>صورة هوية المنظم</p>
                        </div>
                    </label>
                    <div id="organizer-logo-preview" class="media-preview-grid"></div>
                    <input name="logo_url" value="{{ old('logo_url', $organizer->logo_url) }}" placeholder="أو رابط الشعار" class="w-full rounded-2xl border-slate-200">
                    @if($organizer->logo_url)
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $organizer->logo_url }}" alt="Organizer logo" class="h-36 w-full object-cover">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_logo" value="1">
                                حذف الشعار الحالي
                            </label>
                        </div>
                    @endif
                </div>
                <input name="website_url" value="{{ old('website_url', $organizer->website_url) }}" placeholder="الموقع الإلكتروني" class="w-full rounded-2xl border-slate-200">
                <input name="instagram_url" value="{{ old('instagram_url', $organizer->instagram_url) }}" placeholder="رابط إنستغرام" class="w-full rounded-2xl border-slate-200">
                <input name="x_url" value="{{ old('x_url', $organizer->x_url) }}" placeholder="رابط X / تويتر" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <textarea name="bio" rows="6" placeholder="نبذة عن المنظم" class="w-full rounded-2xl border-slate-200">{{ old('bio', $organizer->bio) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $organizer->is_active ?? true))> منظم نشط / معتمد</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">سيتم تحديث بيانات المنظم وحالة اعتماده.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.organizers.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ المنظم</button>
            </div>
        </div>
    </form>
</section>
@endsection
