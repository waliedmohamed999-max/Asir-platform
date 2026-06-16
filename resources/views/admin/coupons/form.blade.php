@extends('layouts.admin')

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر الكوبونات</span>
                <h2 class="admin-page-title">{{ $coupon->exists ? 'تعديل الكوبون' : 'إضافة كوبون' }}</h2>
                <p class="admin-page-description">أدخل كود الخصم ونوعه وفترة التفعيل وحد الاستخدام بشكل واضح وآمن.</p>
            </div>
            <a href="{{ route('admin.coupons.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="admin-form space-y-6">
        @csrf
        @if($coupon->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">بيانات الكوبون</h3>
            <div class="mt-6 space-y-4">
                <input name="code" value="{{ old('code', $coupon->code) }}" placeholder="كود الخصم" class="w-full rounded-2xl border-slate-200">
                <div class="grid gap-4 md:grid-cols-2">
                    <select name="type" class="w-full rounded-2xl border-slate-200">
                        <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed</option>
                        <option value="percentage" @selected(old('type', $coupon->type) === 'percentage')>Percentage</option>
                    </select>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" placeholder="القيمة" class="w-full rounded-2xl border-slate-200">
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border-slate-200">
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="حد الاستخدام" class="w-full rounded-2xl border-slate-200">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active))> كوبون نشط</label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">تحقق من فترة صلاحية الكوبون وحد الاستخدام.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.coupons.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ</button>
            </div>
        </div>
    </form>
</section>
@endsection
