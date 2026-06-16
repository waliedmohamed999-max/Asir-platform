@extends('layouts.admin')

@php
    $pageTitle = $page->exists ? 'تعديل الصفحة' : 'إضافة صفحة جديدة';
    $pageDescription = 'صفحة ثابتة قابلة للنشر العام مع SEO ووصف مختصر ومحتوى كامل.';
    $footerGroups = \App\Models\Page::FOOTER_GROUPS;
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر الصفحات</span>
                <h2 class="admin-page-title">{{ $page->exists ? 'تعديل الصفحة' : 'إضافة صفحة جديدة' }}</h2>
                <p class="admin-page-description">أنشئ صفحة ثابتة قابلة للنشر العام مع وصف مختصر ومحتوى كامل وSEO.</p>
            </div>
            <a href="{{ route('admin.pages.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="admin-form space-y-6">
        @csrf
        @if($page->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="title" value="{{ old('title', $page->title) }}" placeholder="عنوان الصفحة" class="w-full rounded-2xl border-slate-200">
                <input name="slug" value="{{ old('slug', $page->slug) }}" placeholder="Slug اختياري" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 space-y-4">
                <textarea name="excerpt" rows="3" placeholder="وصف مختصر" class="w-full rounded-2xl border-slate-200">{{ old('excerpt', $page->excerpt) }}</textarea>
                <textarea name="body" rows="14" placeholder="محتوى الصفحة" class="w-full rounded-2xl border-slate-200">{{ old('body', $page->body) }}</textarea>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">SEO والحالة</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" placeholder="Meta title" class="w-full rounded-2xl border-slate-200">
                <input name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" placeholder="Meta description" class="w-full rounded-2xl border-slate-200 md:col-span-2">
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" placeholder="الترتيب" class="w-full rounded-2xl border-slate-200">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active ?? true))> صفحة منشورة</label>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">ظهور الصفحة في الفوتر</h3>
            <p class="mt-1 text-sm text-slate-500">فعّل هذا القسم إذا أردت أن تظهر الصفحة كرابط داخل أحد أعمدة الفوتر، مع إمكانية تخصيص النص والرابط والفتح في تبويب جديد.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3">
                    <input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', $page->show_in_footer ?? false))>
                    إظهار هذه الصفحة في الفوتر
                </label>

                <label class="space-y-2">
                    <span class="text-sm font-bold text-slate-700">عمود الفوتر</span>
                    <select name="footer_group" class="w-full rounded-2xl border-slate-200">
                        <option value="">بدون عمود</option>
                        @foreach($footerGroups as $value => $label)
                            <option value="{{ $value }}" @selected(old('footer_group', $page->footer_group) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input name="footer_label" value="{{ old('footer_label', $page->footer_label) }}" placeholder="اسم الرابط في الفوتر - اختياري" class="w-full rounded-2xl border-slate-200">
                <input name="target_url" value="{{ old('target_url', $page->target_url) }}" placeholder="رابط خارجي/داخلي اختياري بدل صفحة المحتوى" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3">
                    <input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $page->open_in_new_tab ?? false))>
                    فتح الرابط في تبويب جديد
                </label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">سيتم تحديث الصفحة وروابط الفوتر المرتبطة بها.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pages.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ الصفحة</button>
            </div>
        </div>
    </form>
</section>
@endsection
