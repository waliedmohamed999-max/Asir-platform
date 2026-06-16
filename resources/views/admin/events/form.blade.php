@extends('layouts.admin')

@php
    $pageTitle = $event->exists ? 'تعديل الفعالية' : 'إضافة فعالية جديدة';
    $pageDescription = 'إدارة بيانات الفعالية، الصور، الحقول التسويقية، وأنواع التذاكر من شاشة واحدة.';

    $galleryValue = old('gallery_images', '');
    $existingGalleryImages = old('existing_gallery_images', $event->images->pluck('image_url')->all());
    $faqRows = old('faqs', $event->faqs ?? [['question' => '', 'answer' => ''], ['question' => '', 'answer' => '']]);
    $ticketRows = old('tickets', $event->tickets->map(function ($ticket) {
        return [
            'name' => $ticket->name,
            'type' => $ticket->type,
            'price' => $ticket->price,
            'price_before_discount' => $ticket->price_before_discount,
            'quantity' => $ticket->quantity,
            'description' => $ticket->description,
            'features' => is_array($ticket->features) ? implode("\n", $ticket->features) : '',
            'purchase_limit_per_user' => $ticket->purchase_limit_per_user,
            'label_color' => $ticket->label_color,
            'sort_order' => $ticket->sort_order,
            'status' => $ticket->status,
            'starts_at' => optional($ticket->starts_at)->format('Y-m-d\TH:i'),
            'ends_at' => optional($ticket->ends_at)->format('Y-m-d\TH:i'),
            'is_active' => $ticket->is_active,
            'is_hidden' => $ticket->is_hidden,
            'uses_qr' => $ticket->uses_qr,
        ];
    })->values()->all());

    if (blank($ticketRows)) {
        $ticketRows = [
            ['name' => 'Regular', 'type' => 'regular', 'price' => '', 'price_before_discount' => '', 'quantity' => '', 'description' => '', 'features' => '', 'purchase_limit_per_user' => '', 'label_color' => '#1d4ed8', 'sort_order' => 1, 'status' => 'active', 'starts_at' => '', 'ends_at' => '', 'is_active' => true, 'is_hidden' => false, 'uses_qr' => true],
            ['name' => 'VIP', 'type' => 'vip', 'price' => '', 'price_before_discount' => '', 'quantity' => '', 'description' => '', 'features' => '', 'purchase_limit_per_user' => '', 'label_color' => '#7c3aed', 'sort_order' => 2, 'status' => 'active', 'starts_at' => '', 'ends_at' => '', 'is_active' => true, 'is_hidden' => false, 'uses_qr' => true],
        ];
    }
@endphp

@push('styles')
    <style>
        .ticket-studio-shell {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background:
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.1), transparent 32%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92));
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
        }

        .ticket-studio-shell::before {
            content: '';
            position: absolute;
            inset-inline-start: 0;
            top: 28px;
            bottom: 28px;
            width: 4px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--ticket-color, #7c3aed), #e8356d);
        }

        .ticket-preview-card {
            position: relative;
            overflow: hidden;
            min-height: 100%;
            border-radius: 26px;
            color: #fff;
            background:
                radial-gradient(circle at 20% 18%, rgba(255, 255, 255, 0.22), transparent 28%),
                linear-gradient(145deg, var(--ticket-color, #7c3aed), #0f172a 72%);
            box-shadow: 0 24px 54px rgba(15, 23, 42, 0.22);
        }

        .ticket-preview-card::before,
        .ticket-preview-card::after {
            content: '';
            position: absolute;
            top: 54%;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #f8fafc;
            transform: translateY(-50%);
        }

        .ticket-preview-card::before { right: -12px; }
        .ticket-preview-card::after { left: -12px; }

        .ticket-preview-card .ticket-perforation {
            border-top: 1px dashed rgba(255, 255, 255, 0.38);
        }

        .ticket-editor-field label {
            margin-bottom: 0.5rem;
            display: block;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .ticket-editor-field input,
        .ticket-editor-field select,
        .ticket-editor-field textarea {
            width: 100%;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.88);
            padding: 0.85rem 1rem;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .ticket-editor-field input:focus,
        .ticket-editor-field select:focus,
        .ticket-editor-field textarea:focus {
            border-color: rgba(124, 58, 237, 0.55);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .ticket-toggle-tile {
            border: 1px solid #e2e8f0;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .ticket-toggle-tile:hover {
            border-color: rgba(124, 58, 237, 0.35);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('content')
<section class="space-y-6">
    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-black">تعذر حفظ الفعالية. راجع الحقول التالية:</p>
            <ul class="mt-2 space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-card admin-page-head">
        <div class="page-actions">
            <div>
                <span class="admin-page-kicker">محرر الفعاليات</span>
                <h2 class="admin-page-title">{{ $event->exists ? 'تعديل الفعالية' : 'إضافة فعالية جديدة' }}</h2>
                <p class="admin-page-description">نموذج موحد لإدارة بيانات الفعالية، المحتوى، SEO، الأسئلة الشائعة، وأنواع التذاكر من شاشة واحدة.</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="admin-secondary-btn">رجوع للقائمة</a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}" class="admin-form space-y-6">
        @csrf
        @if($event->exists) @method('PUT') @endif

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">البيانات الأساسية</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-bold">اسم الفعالية بالعربي</label>
                    <input name="title" value="{{ old('title', $event->title) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-bold">اسم الفعالية بالإنجليزي</label>
                    <input name="title_en" value="{{ old('title_en', $event->title_en) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">المدينة</label>
                    <select name="city_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $event->city_id) == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">التصنيف</label>
                    <select name="category_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $event->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">الحالة</label>
                    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $event->status ?: 'draft') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">السعة</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold">المكان بالعربي</label>
                    <input name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">المكان بالإنجليزي</label>
                    <input name="venue_name_en" value="{{ old('venue_name_en', $event->venue_name_en) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-bold">البداية</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', optional($event->start_date)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">النهاية</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', optional($event->end_date)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">ترتيب الظهور</label>
                    <input type="number" name="display_order" value="{{ old('display_order', $event->display_order) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div class="grid gap-3 pt-8 sm:grid-cols-2">
                    <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $event->is_featured))> مميزة</label>
                    <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="show_on_homepage" value="1" @checked(old('show_on_homepage', $event->show_on_homepage))> الرئيسية</label>
                    <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true))> مفعلة</label>
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">الوصف والمحتوى</h2>
            <div class="mt-6 space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-bold">وصف قصير</label>
                    <textarea name="excerpt" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('excerpt', $event->excerpt) }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">الوصف الكامل</label>
                    <textarea name="description" rows="6" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('description', $event->description) }}</textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold">الجدول / الملاحظات</label>
                        <textarea name="schedule_notes" rows="5" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('schedule_notes', $event->schedule_notes) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">الشروط والأحكام</label>
                        <textarea name="terms" rows="5" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('terms', $event->terms) }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">سياسة الاسترجاع</label>
                    <textarea name="refund_policy" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('refund_policy', $event->refund_policy) }}</textarea>
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">الوسائط والموقع و SEO</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-bold">رفع صور المعرض</label>
                        <label class="media-uploader" data-media-upload data-preview-target="event-gallery-preview" data-placeholder="اسحب صور المعرض هنا أو اضغط للاختيار">
                            <input type="file" name="gallery_files[]" multiple accept="image/*" class="sr-only">
                            <div class="space-y-2 text-center">
                                <p class="text-sm font-black text-slate-800">اسحب صور المعرض هنا أو اضغط للاختيار</p>
                                <p class="text-xs text-slate-500" data-media-text>PNG / JPG / WEBP - يمكن اختيار أكثر من صورة</p>
                            </div>
                        </label>
                        <div id="event-gallery-preview" class="media-preview-grid"></div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">إضافة صور من روابط خارجية - كل رابط في سطر</label>
                        <textarea name="gallery_images" rows="6" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ $galleryValue }}</textarea>
                    </div>
                    @if(! empty($existingGalleryImages))
                        <div class="rounded-[1.5rem] border border-slate-200 p-4">
                            <p class="mb-3 text-sm font-bold text-slate-700">الصور الحالية</p>
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($existingGalleryImages as $image)
                                    <label class="overflow-hidden rounded-[1.25rem] border border-slate-200">
                                        <img src="{{ $image }}" alt="Event image" class="h-32 w-full object-cover">
                                        <div class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-slate-700">
                                            <input type="checkbox" name="existing_gallery_images[]" value="{{ $image }}" checked>
                                            الإبقاء على هذه الصورة
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-bold">رفع Banner</label>
                        <label class="media-uploader" data-media-upload data-preview-target="event-banner-preview" data-placeholder="اسحب صورة البنر هنا أو اضغط للاختيار">
                            <input type="file" name="banner_image" accept="image/*" class="sr-only">
                            <div class="space-y-2 text-center">
                                <p class="text-sm font-black text-slate-800">اسحب صورة البنر هنا أو اضغط للاختيار</p>
                                <p class="text-xs text-slate-500" data-media-text>صورة رئيسية للفعالية</p>
                            </div>
                        </label>
                        <div id="event-banner-preview" class="media-preview-grid"></div>
                    </div>
                    @if($event->banner_image_url)
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                            <img src="{{ $event->banner_image_url }}" alt="Banner" class="h-44 w-full object-cover">
                            <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                <input type="checkbox" name="remove_banner_image" value="1">
                                حذف البنر الحالي
                            </label>
                        </div>
                    @endif
                    <div>
                        <label class="mb-2 block text-sm font-bold">أو رابط Banner خارجي</label>
                        <input name="banner_image_url" value="{{ old('banner_image_url', $event->banner_image_url) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">Video URL</label>
                        <input name="video_url" value="{{ old('video_url', $event->video_url) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">Google Maps URL</label>
                        <input name="map_url" value="{{ old('map_url', $event->map_url) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">Latitude</label>
                    <input name="location_lat" value="{{ old('location_lat', $event->location_lat) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">Longitude</label>
                    <input name="location_lng" value="{{ old('location_lng', $event->location_lng) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">Meta title</label>
                    <input name="meta_title" value="{{ old('meta_title', $event->meta_title) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">Meta description</label>
                    <input name="meta_description" value="{{ old('meta_description', $event->meta_description) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <h2 class="text-2xl font-black">الأسئلة الشائعة</h2>
            <div class="mt-6 grid gap-4">
                @foreach($faqRows as $index => $faq)
                    <div class="grid gap-4 rounded-[1.5rem] border border-slate-200 p-4 md:grid-cols-2">
                        <input name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="السؤال" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                        <textarea name="faqs[{{ $index }}][answer]" rows="2" placeholder="الإجابة" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ $faq['answer'] ?? '' }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="admin-card admin-form-card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black">أنواع التذاكر</h2>
                    <p class="mt-1 text-sm text-slate-500">صمم التذاكر كما ستظهر في التطبيق: السعر، اللون، المخزون، المميزات، وفترة البيع.</p>
                </div>
                <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm font-black text-violet-700">
                    {{ count($ticketRows) }} نوع تذكرة مرتبط بالتطبيق
                </div>
            </div>
            <div class="mt-6 space-y-5">
                @foreach($ticketRows as $index => $ticket)
                    @php
                        $ticketColor = $ticket['label_color'] ?? '#7c3aed';
                        $ticketName = $ticket['name'] ?? 'تذكرة جديدة';
                        $ticketType = $ticket['type'] ?? 'regular';
                        $ticketStatus = $ticket['status'] ?? 'active';
                        $statusLabels = ['active' => 'متاحة للبيع', 'inactive' => 'متوقفة', 'sold_out' => 'نفدت'];
                        $statusColors = ['active' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'inactive' => 'bg-slate-100 text-slate-600 border-slate-200', 'sold_out' => 'bg-rose-50 text-rose-700 border-rose-200'];
                        $ticketFeatures = collect(preg_split('/\r\n|\r|\n/', (string) ($ticket['features'] ?? '')))->map(fn ($feature) => trim($feature))->filter()->take(4);
                        $ticketPrice = is_numeric($ticket['price'] ?? null) ? (float) $ticket['price'] : null;
                        $beforePrice = is_numeric($ticket['price_before_discount'] ?? null) ? (float) $ticket['price_before_discount'] : null;
                        $discount = ($ticketPrice !== null && $beforePrice && $beforePrice > $ticketPrice) ? round((($beforePrice - $ticketPrice) / $beforePrice) * 100) : null;
                    @endphp
                    <div class="ticket-studio-shell rounded-[2rem] p-4 md:p-5" style="--ticket-color: {{ $ticketColor }}">
                        <div class="grid gap-5 xl:grid-cols-[330px_minmax(0,1fr)]">
                            <aside class="ticket-preview-card p-5">
                                <div class="relative z-10 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/65">Aseer Ticket</p>
                                        <h3 class="mt-2 text-2xl font-black leading-tight">{{ $ticketName }}</h3>
                                    </div>
                                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-black backdrop-blur">{{ $ticketType }}</span>
                                </div>

                                <div class="relative z-10 mt-8 rounded-3xl bg-white/12 p-4 backdrop-blur">
                                    <p class="text-xs font-bold text-white/65">السعر داخل التطبيق</p>
                                    <div class="mt-2 flex flex-wrap items-end gap-2">
                                        <span class="text-4xl font-black">{{ $ticketPrice !== null ? number_format($ticketPrice, 2) : '0.00' }}</span>
                                        <span class="pb-1 text-sm font-bold text-white/70">ر.س</span>
                                        @if($beforePrice && $beforePrice > ($ticketPrice ?? 0))
                                            <span class="pb-1 text-sm font-bold text-white/45 line-through">{{ number_format($beforePrice, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($discount)
                                        <span class="mt-3 inline-flex rounded-full bg-rose-500 px-3 py-1 text-xs font-black">خصم {{ $discount }}%</span>
                                    @endif
                                </div>

                                <div class="ticket-perforation relative z-10 my-5"></div>

                                <div class="relative z-10 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-2xl bg-white/10 p-3">
                                        <p class="text-white/55">المخزون</p>
                                        <p class="mt-1 text-lg font-black">{{ $ticket['quantity'] ?: 'غير محدد' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white/10 p-3">
                                        <p class="text-white/55">حد الشراء</p>
                                        <p class="mt-1 text-lg font-black">{{ $ticket['purchase_limit_per_user'] ?: 'مفتوح' }}</p>
                                    </div>
                                </div>

                                <div class="relative z-10 mt-4 flex flex-wrap gap-2">
                                    @forelse($ticketFeatures as $feature)
                                        <span class="rounded-full bg-white/12 px-3 py-1 text-xs font-bold text-white/85">{{ $feature }}</span>
                                    @empty
                                        <span class="rounded-full bg-white/12 px-3 py-1 text-xs font-bold text-white/65">أضف مميزات التذكرة</span>
                                    @endforelse
                                </div>

                                <div class="relative z-10 mt-5 flex items-center justify-between rounded-2xl bg-black/20 px-4 py-3">
                                    <span class="text-xs font-black text-white/70">ترتيب العرض #{{ $ticket['sort_order'] ?? ($index + 1) }}</span>
                                    <span class="text-xs font-black">{{ ($ticket['uses_qr'] ?? true) ? 'QR فعال' : 'بدون QR' }}</span>
                                </div>
                            </aside>

                            <div class="space-y-5">
                                <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl bg-white/70 px-4 py-3">
                                    <div>
                                        <p class="text-xs font-black text-slate-400">تذكرة رقم {{ $index + 1 }}</p>
                                        <p class="mt-1 text-lg font-black text-slate-950">{{ $ticketName }}</p>
                                    </div>
                                    <span class="rounded-full border px-3 py-1 text-xs font-black {{ $statusColors[$ticketStatus] ?? $statusColors['active'] }}">
                                        {{ $statusLabels[$ticketStatus] ?? $ticketStatus }}
                                    </span>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div class="ticket-editor-field xl:col-span-2">
                                        <label>اسم التذكرة</label>
                                        <input name="tickets[{{ $index }}][name]" value="{{ $ticket['name'] ?? '' }}" placeholder="مثال: دخول عادي / VIP">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>نوع التذكرة</label>
                                        <select name="tickets[{{ $index }}][type]">
                                            @foreach($ticketTypes as $type)
                                                <option value="{{ $type }}" @selected(($ticket['type'] ?? 'regular') === $type)>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>حالة البيع</label>
                                        <select name="tickets[{{ $index }}][status]">
                                            @foreach(['active', 'inactive', 'sold_out'] as $status)
                                                <option value="{{ $status }}" @selected(($ticket['status'] ?? 'active') === $status)>{{ $statusLabels[$status] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>السعر</label>
                                        <input type="number" step="0.01" name="tickets[{{ $index }}][price]" value="{{ $ticket['price'] ?? '' }}" placeholder="0.00">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>السعر قبل الخصم</label>
                                        <input type="number" step="0.01" name="tickets[{{ $index }}][price_before_discount]" value="{{ $ticket['price_before_discount'] ?? '' }}" placeholder="اختياري">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>الكمية المتاحة</label>
                                        <input type="number" name="tickets[{{ $index }}][quantity]" value="{{ $ticket['quantity'] ?? '' }}" placeholder="عدد التذاكر">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>حد الشراء للمستخدم</label>
                                        <input type="number" name="tickets[{{ $index }}][purchase_limit_per_user]" value="{{ $ticket['purchase_limit_per_user'] ?? '' }}" placeholder="مثال: 4">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>لون التذكرة في التطبيق</label>
                                        <input type="color" name="tickets[{{ $index }}][label_color]" value="{{ $ticketColor }}" class="h-[54px] cursor-pointer p-2">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>ترتيب الظهور</label>
                                        <input type="number" name="tickets[{{ $index }}][sort_order]" value="{{ $ticket['sort_order'] ?? ($index + 1) }}" placeholder="1">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>بداية البيع</label>
                                        <input type="datetime-local" name="tickets[{{ $index }}][starts_at]" value="{{ $ticket['starts_at'] ?? '' }}">
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>نهاية البيع</label>
                                        <input type="datetime-local" name="tickets[{{ $index }}][ends_at]" value="{{ $ticket['ends_at'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="ticket-editor-field">
                                        <label>وصف يظهر تحت التذكرة</label>
                                        <textarea name="tickets[{{ $index }}][description]" rows="4" placeholder="اكتب وصف قصير للتذكرة">{{ $ticket['description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="ticket-editor-field">
                                        <label>مميزات التذكرة</label>
                                        <textarea name="tickets[{{ $index }}][features]" rows="4" placeholder="كل ميزة في سطر">{{ $ticket['features'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="grid gap-3 md:grid-cols-3">
                                    <label class="ticket-toggle-tile flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-black">
                                        <span>مفعلة في التطبيق</span>
                                        <input type="checkbox" name="tickets[{{ $index }}][is_active]" value="1" @checked($ticket['is_active'] ?? true)>
                                    </label>
                                    <label class="ticket-toggle-tile flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-black">
                                        <span>مخفية من الواجهة</span>
                                        <input type="checkbox" name="tickets[{{ $index }}][is_hidden]" value="1" @checked($ticket['is_hidden'] ?? false)>
                                    </label>
                                    <label class="ticket-toggle-tile flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-black">
                                        <span>QR / Barcode</span>
                                        <input type="checkbox" name="tickets[{{ $index }}][uses_qr]" value="1" @checked($ticket['uses_qr'] ?? true)>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="admin-sticky-actions">
            <span class="text-sm font-bold text-slate-500">{{ $event->exists ? 'سيتم تحديث بيانات الفعالية الحالية' : 'سيتم إنشاء فعالية جديدة بعد الحفظ' }}</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.events.index') }}" class="admin-secondary-btn">رجوع</a>
                <button class="admin-primary-btn px-8">حفظ الفعالية</button>
            </div>
        </div>
    </form>
</section>
@endsection
