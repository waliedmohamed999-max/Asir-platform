@extends('layouts.admin')

@php
    $pageTitle = $faq->exists ? 'تعديل السؤال' : 'إضافة سؤال جديد';
    $pageDescription = 'سؤال وجواب يظهران في صفحة الأسئلة الشائعة العامة.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر الأسئلة</span>
                <h2 class="admin-page-title">{{ $faq->exists ? 'تعديل السؤال' : 'إضافة سؤال جديد' }}</h2>
                <p class="admin-page-description">أضف سؤالاً واضحاً وإجابة مناسبة لصفحة الأسئلة الشائعة العامة.</p>
            </div>
            <a href="{{ route('admin.faqs.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="admin-form space-y-6">
        @csrf
        @if($faq->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">المحتوى</h3>
            <div class="mt-6 space-y-4">
                <input name="question" value="{{ old('question', $faq->question) }}" placeholder="السؤال" class="w-full rounded-2xl border-slate-200">
                <textarea name="answer" rows="8" placeholder="الإجابة" class="w-full rounded-2xl border-slate-200">{{ old('answer', $faq->answer) }}</textarea>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <input name="category" value="{{ old('category', $faq->category) }}" placeholder="التصنيف الداخلي مثل: التذاكر / الحجز / الدفع" class="w-full rounded-2xl border-slate-200">
                <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order) }}" placeholder="الترتيب" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true))> سؤال نشط</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">سيظهر السؤال في صفحة المساعدة عند تفعيله.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.faqs.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ السؤال</button>
            </div>
        </div>
    </form>
</section>
@endsection
