@extends('layouts.admin')

@php
    $pageTitle = 'الإعدادات العامة';
    $pageDescription = 'اسم المنصة، الهوية، التواصل، SEO، وسائل الدفع، ونصوص الصفحة الرئيسية.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">إعدادات النظام</span>
                <h2 class="admin-page-title">مركز إعدادات المنصة</h2>
                <p class="admin-page-description">حدث الهوية، التواصل، SEO، والدفع من شاشة موحدة قابلة للتوسع.</p>
            </div>
            <div class="topbar-chip">تُحفظ الإعدادات مباشرة داخل قاعدة البيانات</div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="admin-form space-y-6">
        @csrf
        @method('PUT')

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">الهوية الأساسية</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="platform_name" value="{{ old('platform_name', $settings['platform_name']) }}" placeholder="اسم المنصة" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="platform_tagline" value="{{ old('platform_tagline', $settings['platform_tagline']) }}" placeholder="الوصف المختصر" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <div class="space-y-3">
                    <label class="media-uploader" data-media-upload data-preview-target="platform-logo-preview" data-placeholder="اسحب شعار المنصة هنا أو اضغط للاختيار">
                        <input type="file" name="platform_logo_file" accept="image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب شعار المنصة هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>الشعار الأساسي للواجهة والداشبورد</p>
                        </div>
                    </label>
                    <div id="platform-logo-preview" class="media-preview-grid"></div>
                    <input name="platform_logo_url" value="{{ old('platform_logo_url', $settings['platform_logo_url']) }}" placeholder="أو رابط الشعار" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    @if($settings['platform_logo_url'])
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $settings['platform_logo_url'] }}" alt="Platform logo" class="h-36 w-full object-contain bg-white p-4">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_platform_logo" value="1">
                                حذف الشعار الحالي
                            </label>
                        </div>
                    @endif
                </div>
                <div class="space-y-3">
                    <label class="media-uploader" data-media-upload data-preview-target="platform-favicon-preview" data-placeholder="اسحب الفافيكون هنا أو اضغط للاختيار">
                        <input type="file" name="platform_favicon_file" accept=".ico,image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب الفافيكون هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>أيقونة المتصفح والهوية المختصرة</p>
                        </div>
                    </label>
                    <div id="platform-favicon-preview" class="media-preview-grid"></div>
                    <input name="platform_favicon_url" value="{{ old('platform_favicon_url', $settings['platform_favicon_url']) }}" placeholder="أو رابط الفافيكون" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    @if($settings['platform_favicon_url'])
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $settings['platform_favicon_url'] }}" alt="Favicon" class="h-36 w-full object-contain bg-white p-6">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_platform_favicon" value="1">
                                حذف الفافيكون الحالي
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">التواصل والمنصة</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <input name="support_email" value="{{ old('support_email', $settings['support_email']) }}" placeholder="البريد الرسمي" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="support_phone" value="{{ old('support_phone', $settings['support_phone']) }}" placeholder="رقم خدمة العملاء" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="support_whatsapp" value="{{ old('support_whatsapp', $settings['support_whatsapp']) }}" placeholder="واتساب" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="platform_address" value="{{ old('platform_address', $settings['platform_address']) }}" placeholder="العنوان" class="w-full rounded-2xl border border-slate-200 px-4 py-3 xl:col-span-2">
                <input name="default_currency" value="{{ old('default_currency', $settings['default_currency']) }}" placeholder="العملة" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="default_locale" value="{{ old('default_locale', $settings['default_locale']) }}" placeholder="اللغة الافتراضية" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="service_fee" value="{{ old('service_fee', $settings['service_fee']) }}" placeholder="رسوم الخدمة" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="tax_percentage" value="{{ old('tax_percentage', $settings['tax_percentage']) }}" placeholder="الضريبة %" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">SEO والسوشيال</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="seo_meta_title" value="{{ old('seo_meta_title', $settings['seo_meta_title']) }}" placeholder="SEO Meta Title" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="seo_meta_description" value="{{ old('seo_meta_description', $settings['seo_meta_description']) }}" placeholder="SEO Meta Description" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="رابط Instagram" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="social_x" value="{{ old('social_x', $settings['social_x']) }}" placeholder="رابط X / تويتر" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="social_tiktok" value="{{ old('social_tiktok', $settings['social_tiktok']) }}" placeholder="رابط TikTok" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                <input name="social_snapchat" value="{{ old('social_snapchat', $settings['social_snapchat']) }}" placeholder="رابط Snapchat" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">إعدادات الدفع والمحتوى</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="space-y-3">
                    <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="payment_stripe_enabled" value="1" @checked(old('payment_stripe_enabled', $settings['payment_stripe_enabled']))> تفعيل Stripe</label>
                    <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="payment_paypal_enabled" value="1" @checked(old('payment_paypal_enabled', $settings['payment_paypal_enabled']))> تفعيل PayPal</label>
                    <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3"><input type="checkbox" name="payment_mada_enabled" value="1" @checked(old('payment_mada_enabled', $settings['payment_mada_enabled']))> تفعيل مدى</label>
                </div>
                <div class="space-y-4">
                    <input name="homepage_hero_title" value="{{ old('homepage_hero_title', $settings['homepage_hero_title']) }}" placeholder="عنوان الهيرو الرئيسي" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    <textarea name="homepage_hero_subtitle" rows="3" placeholder="وصف الهيرو" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('homepage_hero_subtitle', $settings['homepage_hero_subtitle']) }}</textarea>
                    <textarea name="footer_about" rows="4" placeholder="نص الفوتر / من نحن" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('footer_about', $settings['footer_about']) }}</textarea>
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">عناوين الفوتر والدعم</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <input name="footer_categories_title" value="{{ old('footer_categories_title', $settings['footer_categories_title']) }}" placeholder="عنوان الفئات">
                <input name="footer_about_title" value="{{ old('footer_about_title', $settings['footer_about_title']) }}" placeholder="عنوان من نحن">
                <input name="footer_organizers_title" value="{{ old('footer_organizers_title', $settings['footer_organizers_title']) }}" placeholder="عنوان للمنظمين">
                <input name="footer_services_title" value="{{ old('footer_services_title', $settings['footer_services_title']) }}" placeholder="عنوان الخدمات">
                <input name="footer_partners_title" value="{{ old('footer_partners_title', $settings['footer_partners_title']) }}" placeholder="عنوان للشركاء">
                <input name="footer_apps_title" value="{{ old('footer_apps_title', $settings['footer_apps_title']) }}" placeholder="عنوان تحميل التطبيق">
                <input name="payment_section_title" value="{{ old('payment_section_title', $settings['payment_section_title']) }}" placeholder="عنوان قسم طرق الدفع">
                <input name="customer_service_title" value="{{ old('customer_service_title', $settings['customer_service_title']) }}" placeholder="عنوان خدمة العملاء">
                <input name="support_section_title" value="{{ old('support_section_title', $settings['support_section_title']) }}" placeholder="عنوان قسم الدعم">
                <input name="support_section_subtitle" value="{{ old('support_section_subtitle', $settings['support_section_subtitle']) }}" placeholder="الوصف الفرعي للدعم">
                <input name="support_button_text" value="{{ old('support_button_text', $settings['support_button_text']) }}" placeholder="نص زر فريق الدعم">
                <input name="footer_support_button_text" value="{{ old('footer_support_button_text', $settings['footer_support_button_text']) }}" placeholder="نص زر مركز الدعم">
                <input name="organizer_cta_text" value="{{ old('organizer_cta_text', $settings['organizer_cta_text']) }}" placeholder="نص زر إضافة فعالية" class="xl:col-span-2">
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">تؤثر هذه الإعدادات على الواجهة العامة والتطبيق.</span>
            <button class="admin-primary-btn px-8">حفظ الإعدادات</button>
        </div>
    </form>
</section>
@endsection
