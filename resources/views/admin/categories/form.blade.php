@extends('layouts.admin')

@php
    $pageTitle = $category->exists ? 'تعديل التصنيف' : 'إضافة تصنيف جديد';
    $pageDescription = 'أضف وصفاً وصورة وSEO وترتيب عرض للتصنيف.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر التصنيفات</span>
                <h2 class="admin-page-title">{{ $category->exists ? 'تعديل التصنيف' : 'إضافة تصنيف جديد' }}</h2>
                <p class="admin-page-description">أنشئ تصنيفاً رئيسياً أو فرعياً مع وصف وSEO وترتيب ظهور مناسب داخل المنصة.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="admin-form space-y-6">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="name" value="{{ old('name', $category->name) }}" placeholder="اسم التصنيف" class="w-full rounded-2xl border-slate-200">
                <input name="slug" value="{{ old('slug', $category->slug) }}" placeholder="Slug اختياري" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" placeholder="الاسم العربي في التطبيق" class="w-full rounded-2xl border-slate-200">
                <input name="name_en" value="{{ old('name_en', $category->name_en) }}" placeholder="English name in app" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <select name="parent_id" class="w-full rounded-2xl border-slate-200">
                    <option value="">تصنيف رئيسي</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
                <input name="icon" value="{{ old('icon', $category->icon) }}" placeholder="الأيقونة أو اسمها" class="w-full rounded-2xl border-slate-200">
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" placeholder="الترتيب" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 space-y-4">
                <label class="media-uploader" data-media-upload data-preview-target="category-image-preview" data-placeholder="اسحب صورة التصنيف هنا أو اضغط للاختيار">
                    <input type="file" name="image_file" accept="image/*" class="sr-only">
                    <div class="space-y-2 text-center">
                        <p class="text-sm font-black text-slate-800">اسحب صورة التصنيف هنا أو اضغط للاختيار</p>
                        <p class="text-xs text-slate-500" data-media-text>صورة الغلاف الخاصة بالتصنيف</p>
                    </div>
                </label>
                <div id="category-image-preview" class="media-preview-grid"></div>
                <input name="image_url" value="{{ old('image_url', $category->image_url) }}" placeholder="أو رابط الصورة" class="w-full rounded-2xl border-slate-200">
                @if($category->image_url)
                    <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                        <img src="{{ $category->image_url }}" alt="Category image" class="h-40 w-full object-cover">
                        <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                            <input type="checkbox" name="remove_image" value="1">
                            حذف الصورة الحالية
                        </label>
                    </div>
                @endif
                <textarea name="description" rows="4" placeholder="الوصف" class="w-full rounded-2xl border-slate-200">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">SEO والحالة</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Meta title" class="w-full rounded-2xl border-slate-200">
                <input name="meta_description" value="{{ old('meta_description', $category->meta_description) }}" placeholder="Meta description" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> تصنيف نشط</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">راجع بيانات التصنيف قبل الحفظ.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.categories.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ التصنيف</button>
            </div>
        </div>
    </form>
</section>
@endsection
