@extends('layouts.admin')

@php
    $pageTitle = $homepageItem->exists ? 'تعديل إعلان / عنصر' : 'إضافة إعلان / عنصر جديد';
    $pageDescription = 'مسار واحد لإدارة البنرات، الإعلانات الموجهة، وكروت الصفحة الرئيسية مع الاستهداف والزمن والروابط.';
@endphp

@section('content')
<section class="space-y-6">
    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر المحتوى الديناميكي</span>
                <h2 class="admin-page-title">{{ $homepageItem->exists ? 'تعديل عنصر الصفحة الرئيسية' : 'إضافة عنصر جديد للصفحة الرئيسية' }}</h2>
                <p class="admin-page-description">املأ بيانات البنر أو الكرت أو الإعلان مع الاستهداف والفترة الزمنية وربط الفعالية عند الحاجة.</p>
            </div>
            <a href="{{ route('admin.homepage-items.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $homepageItem->exists ? route('admin.homepage-items.update', $homepageItem) : route('admin.homepage-items.store') }}" class="admin-form space-y-6">
        @csrf
        @if($homepageItem->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h3 class="text-xl font-black">البيانات الأساسية</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <input name="title" value="{{ old('title', $homepageItem->title) }}" placeholder="العنوان" class="w-full rounded-2xl border-slate-200">
                <input name="slug" value="{{ old('slug', $homepageItem->slug) }}" placeholder="Slug اختياري" class="w-full rounded-2xl border-slate-200">
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="space-y-3">
                    <label class="media-uploader" data-media-upload data-preview-target="homepage-card-preview" data-placeholder="اسحب صورة الكرت هنا أو اضغط للاختيار">
                        <input type="file" name="image_file" accept="image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب صورة الكرت هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>الصورة الأساسية للعنصر</p>
                        </div>
                    </label>
                    <div id="homepage-card-preview" class="media-preview-grid"></div>
                    <input name="image_url" value="{{ old('image_url', $homepageItem->image_url) }}" placeholder="أو رابط الصورة" class="w-full rounded-2xl border-slate-200">
                    @if($homepageItem->image_url)
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $homepageItem->image_url }}" alt="Card image" class="h-40 w-full object-cover">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_image" value="1">
                                حذف الصورة الحالية
                            </label>
                        </div>
                    @endif
                </div>
                <div class="space-y-3">
                    <label class="media-uploader" data-media-upload data-preview-target="homepage-hero-preview" data-placeholder="اسحب صورة الهيرو هنا أو اضغط للاختيار">
                        <input type="file" name="hero_image_file" accept="image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب صورة الهيرو هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>صورة البانر أو العرض الكبير</p>
                        </div>
                    </label>
                    <div id="homepage-hero-preview" class="media-preview-grid"></div>
                    <input name="hero_image_url" value="{{ old('hero_image_url', $homepageItem->hero_image_url) }}" placeholder="أو رابط صورة البانر التفصيلية" class="w-full rounded-2xl border-slate-200">
                    @if($homepageItem->hero_image_url)
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $homepageItem->hero_image_url }}" alt="Hero image" class="h-40 w-full object-cover">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_hero_image" value="1">
                                حذف صورة الهيرو الحالية
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <textarea name="subtitle" rows="3" placeholder="الوصف / النص المساعد" class="w-full rounded-2xl border-slate-200">{{ old('subtitle', $homepageItem->subtitle) }}</textarea>
                <label class="media-uploader" data-media-upload data-preview-target="homepage-gallery-preview" data-placeholder="اسحب صور المعرض هنا أو اضغط للاختيار">
                    <input type="file" name="gallery_files[]" multiple accept="image/*" class="sr-only">
                    <div class="space-y-2 text-center">
                        <p class="text-sm font-black text-slate-800">اسحب صور المعرض هنا أو اضغط للاختيار</p>
                        <p class="text-xs text-slate-500" data-media-text>يمكنك اختيار أكثر من صورة</p>
                    </div>
                </label>
                <div id="homepage-gallery-preview" class="media-preview-grid"></div>
                <textarea name="gallery" rows="4" placeholder="روابط إضافية للمعرض - كل رابط في سطر" class="w-full rounded-2xl border-slate-200">{{ old('gallery', '') }}</textarea>
                @if(is_array($homepageItem->gallery) && count($homepageItem->gallery))
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach(old('existing_gallery', $homepageItem->gallery) as $image)
                            <label class="overflow-hidden rounded-[1.25rem] border border-slate-200">
                                <img src="{{ $image }}" alt="Gallery image" class="h-32 w-full object-cover">
                                <div class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-slate-700">
                                    <input type="checkbox" name="existing_gallery[]" value="{{ $image }}" checked>
                                    الإبقاء على هذه الصورة
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <div class="border-b border-slate-100 pb-5">
                <h3 class="text-xl font-black">الاستهداف والعرض</h3>
                <p class="mt-1 text-sm text-slate-500">حدد القسم المستهدف ونوع المحتوى والفترة الزمنية وترتيب الظهور.</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-bold">القسم</label>
                    <select name="section_key" class="w-full rounded-2xl border-slate-200">
                        @foreach($sections as $key => $label)
                            <option value="{{ $key }}" @selected(old('section_key', $homepageItem->section_key) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">نوع الإعلان</label>
                    <select name="ad_type" class="w-full rounded-2xl border-slate-200">
                        <option value="">بدون نوع إعلاني</option>
                        @foreach($adTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('ad_type', $homepageItem->ad_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">نوع المحتوى</label>
                    <select name="content_type" class="w-full rounded-2xl border-slate-200">
                        @foreach($contentTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('content_type', $homepageItem->content_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">الترتيب</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $homepageItem->sort_order) }}" placeholder="0" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold">بداية العرض</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($homepageItem->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">نهاية العرض</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($homepageItem->ends_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <div class="border-b border-slate-100 pb-5">
                <h3 class="text-xl font-black">الربط والموقع</h3>
                <p class="mt-1 text-sm text-slate-500">اربط العنصر بتصنيف أو مدينة أو فعالية، وحدد بيانات المكان والخريطة إن لزم.</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-bold">التصنيف</label>
                    <select name="category_id" class="w-full rounded-2xl border-slate-200">
                        <option value="">بدون تصنيف</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $homepageItem->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">المدينة</label>
                    <select name="city_id" class="w-full rounded-2xl border-slate-200">
                        <option value="">بدون مدينة</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $homepageItem->city_id) == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">فعالية مرتبطة</label>
                    <select name="event_id" class="w-full rounded-2xl border-slate-200">
                        <option value="">بدون فعالية مرتبطة</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @selected(old('event_id', $homepageItem->event_id) == $event->id)>{{ $event->title }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-500">إذا تركتها فارغة ولم تضع رابطاً يدوياً، سيحاول النظام ربط الزر تلقائياً بأقرب فعالية مطابقة حسب المدينة والتصنيف والعنوان.</p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-bold">اسم المكان / Venue</label>
                    <input name="venue_name" value="{{ old('venue_name', $homepageItem->venue_name) }}" placeholder="اسم المكان / Venue" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">نص التاريخ الظاهر</label>
                    <input name="date_label" value="{{ old('date_label', $homepageItem->date_label) }}" placeholder="نص التاريخ الظاهر" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">عنوان الموقع</label>
                    <input name="location_title" value="{{ old('location_title', $homepageItem->location_title) }}" placeholder="عنوان الموقع" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold">كود / وصف الموقع</label>
                    <input name="location_code" value="{{ old('location_code', $homepageItem->location_code) }}" placeholder="كود / وصف الموقع" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">رابط Google Maps embed</label>
                    <input name="map_url" value="{{ old('map_url', $homepageItem->map_url) }}" placeholder="رابط Google Maps embed" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <div class="border-b border-slate-100 pb-5">
                <h3 class="text-xl font-black">محتوى صفحة التفاصيل</h3>
                <p class="mt-1 text-sm text-slate-500">الوصف الكامل وما تشمل الفعالية والمواعيد والشروط وخطوات الوصول.</p>
            </div>

            <div class="mt-6 space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-bold">الوصف الكامل لصفحة التفاصيل</label>
                    <textarea name="description" rows="5" placeholder="الوصف الكامل لصفحة التفاصيل" class="w-full rounded-2xl border-slate-200">{{ old('description', $homepageItem->description) }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">ما تشمل الفعالية - كل نقطة في سطر</label>
                    <textarea name="includes" rows="4" placeholder="ما تشمل الفعالية - كل نقطة في سطر" class="w-full rounded-2xl border-slate-200">{{ old('includes', $homepageItem->includes) }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">المواعيد / الجدول</label>
                    <textarea name="schedule" rows="4" placeholder="المواعيد / الجدول" class="w-full rounded-2xl border-slate-200">{{ old('schedule', $homepageItem->schedule) }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">الشروط والأحكام</label>
                    <textarea name="terms" rows="5" placeholder="الشروط والأحكام" class="w-full rounded-2xl border-slate-200">{{ old('terms', $homepageItem->terms) }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">كيف تصل إلى وجهتك؟</label>
                    <textarea name="directions" rows="4" placeholder="كيف تصل إلى وجهتك؟" class="w-full rounded-2xl border-slate-200">{{ old('directions', $homepageItem->directions) }}</textarea>
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <div class="border-b border-slate-100 pb-5">
                <h3 class="text-xl font-black">بيانات العرض والزر</h3>
                <p class="mt-1 text-sm text-slate-500">السعر، النصوص المختصرة، البادج، التقييم، وإعدادات الزر والنشر.</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-bold">السعر / Price Label</label>
                    <input name="price_label" value="{{ old('price_label', $homepageItem->price_label) }}" placeholder="السعر / Price Label" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">الوصف المختصر / Meta</label>
                    <input name="meta_label" value="{{ old('meta_label', $homepageItem->meta_label) }}" placeholder="الوصف المختصر / Meta" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">Badge</label>
                    <input name="badge" value="{{ old('badge', $homepageItem->badge) }}" placeholder="Badge مثل حصري / جديد" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">التقييم</label>
                    <input type="number" step="0.1" min="0" max="5" name="rating" value="{{ old('rating', $homepageItem->rating) }}" placeholder="التقييم" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold">نص الزر</label>
                    <input name="cta_label" value="{{ old('cta_label', $homepageItem->cta_label) }}" placeholder="نص الزر" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">رابط الزر</label>
                    <input name="cta_url" value="{{ old('cta_url', $homepageItem->cta_url) }}" placeholder="رابط الزر" class="w-full rounded-2xl border-slate-200">
                    <p class="mt-2 text-xs text-slate-500">اترك الحقل فارغاً إذا كنت تريد توليد رابط الحجز تلقائياً من الفعالية المرتبطة أو الفعالية المطابقة الأقرب.</p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3">
                    <input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $homepageItem->open_in_new_tab ?? false))>
                    فتح الرابط في نافذة جديدة
                </label>
                <label class="admin-checkbox-tile flex items-center gap-2 px-4 py-3">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $homepageItem->is_active ?? true))>
                    عنصر نشط ويظهر في الصفحة الرئيسية
                </label>
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">سيتم مزامنة هذا العنصر مع الواجهة العامة والتطبيق حسب القسم المحدد.</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.homepage-items.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn">حفظ العنصر</button>
            </div>
        </div>
    </form>
</section>
@endsection
