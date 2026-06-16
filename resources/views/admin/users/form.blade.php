@extends('layouts.admin')

@php
    $pageTitle = $user->exists ? 'تعديل المستخدم' : 'إضافة مستخدم جديد';
    $pageDescription = 'إنشاء مستخدم أو موظف داخلي مع الدور المناسب وتفعيل الحساب.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر المستخدمين</span>
                <h2 class="admin-page-title">{{ $user->exists ? 'تعديل المستخدم' : 'إضافة مستخدم جديد' }}</h2>
                <p class="admin-page-description">أنشئ مستخدماً أو موظفاً داخلياً وحدد دوره وبيانات تواصله وحالة الحساب.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="admin-form space-y-6">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="name" value="{{ old('name', $user->name) }}" placeholder="الاسم" class="w-full rounded-2xl border-slate-200">
                <input name="email" value="{{ old('email', $user->email) }}" placeholder="البريد الإلكتروني" class="w-full rounded-2xl border-slate-200">
                <input name="phone" value="{{ old('phone', $user->phone) }}" placeholder="الجوال" class="w-full rounded-2xl border-slate-200">
                <select name="role" class="w-full rounded-2xl border-slate-200">
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $user->role ?: 'customer') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input type="password" name="password" placeholder="{{ $user->exists ? 'كلمة مرور جديدة اختيارية' : 'كلمة المرور' }}" class="w-full rounded-2xl border-slate-200">
                <input type="password" name="password_confirmation" placeholder="تأكيد كلمة المرور" class="w-full rounded-2xl border-slate-200">
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">التواصل والملف التعريفي</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="space-y-3">
                    <label class="media-uploader" data-media-upload data-preview-target="user-logo-preview" data-placeholder="اسحب شعار المستخدم هنا أو اضغط للاختيار">
                        <input type="file" name="logo_file" accept="image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب شعار المستخدم هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>صورة الملف التعريفي أو الشعار</p>
                        </div>
                    </label>
                    <div id="user-logo-preview" class="media-preview-grid"></div>
                    <input name="logo_url" value="{{ old('logo_url', $user->logo_url) }}" placeholder="أو رابط الشعار أو الصورة" class="w-full rounded-2xl border-slate-200">
                    @if($user->logo_url)
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $user->logo_url }}" alt="User logo" class="h-36 w-full object-cover">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_logo" value="1">
                                حذف الشعار الحالي
                            </label>
                        </div>
                    @endif
                </div>
                <input name="website_url" value="{{ old('website_url', $user->website_url) }}" placeholder="الموقع الإلكتروني" class="w-full rounded-2xl border-slate-200">
                <input name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="واتساب" class="w-full rounded-2xl border-slate-200">
                <input name="instagram_url" value="{{ old('instagram_url', $user->instagram_url) }}" placeholder="رابط إنستغرام" class="w-full rounded-2xl border-slate-200">
                <input name="x_url" value="{{ old('x_url', $user->x_url) }}" placeholder="رابط X / تويتر" class="w-full rounded-2xl border-slate-200 md:col-span-2">
            </div>

            <div class="mt-4">
                <textarea name="bio" rows="5" placeholder="نبذة مختصرة" class="w-full rounded-2xl border-slate-200">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> الحساب نشط</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">تأكد من الدور وحالة الحساب قبل الحفظ.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ المستخدم</button>
            </div>
        </div>
    </form>
</section>
@endsection
