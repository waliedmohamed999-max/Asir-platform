@extends('layouts.app')

@php
    $galleryValue = old('gallery_images', '');
    $existingGalleryImages = old('existing_gallery_images', $event->images->pluck('image_url')->all());
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
        ];
    }
@endphp

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
    <div class="mb-6">
        <h1 class="text-3xl font-black">{{ $event->exists ? 'تعديل الفعالية' : 'إضافة فعالية جديدة' }}</h1>
        <p class="mt-2 text-sm text-slate-500">أنشئ فعالية كاملة مع التذاكر والمحتوى التسويقي من نفس الصفحة.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-black">تعذر حفظ الفعالية. راجع الحقول التالية:</p>
            <ul class="mt-2 space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $event->exists ? route('organizer.events.update', $event) : route('organizer.events.store') }}" class="space-y-6">
        @csrf
        @if($event->exists) @method('PUT') @endif

        <div class="rounded-[2rem] bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-black">البيانات الأساسية</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-bold">اسم الفعالية بالعربي</label>
                    <input name="title" value="{{ old('title', $event->title) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-bold">اسم الفعالية بالإنجليزي</label>
                    <input name="title_en" value="{{ old('title_en', $event->title_en) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">المدينة</label>
                    <select name="city_id" class="w-full rounded-2xl border-slate-200">
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $event->city_id) == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">التصنيف</label>
                    <select name="category_id" class="w-full rounded-2xl border-slate-200">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $event->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">الحالة</label>
                    <select name="status" class="w-full rounded-2xl border-slate-200">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $event->status ?: 'draft') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">السعة</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold">المكان بالعربي</label>
                    <input name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">المكان بالإنجليزي</label>
                    <input name="venue_name_en" value="{{ old('venue_name_en', $event->venue_name_en) }}" class="w-full rounded-2xl border-slate-200">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-bold">البداية</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', optional($event->start_date)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">النهاية</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', optional($event->end_date)->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold">ترتيب الظهور</label>
                    <input type="number" name="display_order" value="{{ old('display_order', $event->display_order) }}" class="w-full rounded-2xl border-slate-200">
                </div>
                <div class="grid gap-3 pt-8 sm:grid-cols-2">
                    <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $event->is_featured))> مميزة</label>
                    <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true))> مفعلة</label>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-black">المحتوى والوسائط</h2>
            <div class="mt-6 space-y-4">
                <textarea name="excerpt" rows="3" class="w-full rounded-2xl border-slate-200" placeholder="وصف مختصر">{{ old('excerpt', $event->excerpt) }}</textarea>
                <textarea name="description" rows="6" class="w-full rounded-2xl border-slate-200" placeholder="الوصف الكامل">{{ old('description', $event->description) }}</textarea>
                <div class="grid gap-4 md:grid-cols-2">
                    <textarea name="schedule_notes" rows="4" class="w-full rounded-2xl border-slate-200" placeholder="جدول أو ملاحظات">{{ old('schedule_notes', $event->schedule_notes) }}</textarea>
                    <textarea name="terms" rows="4" class="w-full rounded-2xl border-slate-200" placeholder="الشروط والأحكام">{{ old('terms', $event->terms) }}</textarea>
                </div>
                <textarea name="refund_policy" rows="4" class="w-full rounded-2xl border-slate-200" placeholder="سياسة الاسترجاع">{{ old('refund_policy', $event->refund_policy) }}</textarea>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-3">
                        <label class="media-uploader" data-media-upload data-preview-target="organizer-event-banner-preview" data-placeholder="اسحب صورة البنر هنا أو اضغط للاختيار">
                            <input type="file" name="banner_image" accept="image/*" class="sr-only">
                            <div class="space-y-2 text-center">
                                <p class="text-sm font-black text-slate-800">اسحب صورة البنر هنا أو اضغط للاختيار</p>
                                <p class="text-xs text-slate-500" data-media-text>صورة رئيسية للفعالية</p>
                            </div>
                        </label>
                        <div id="organizer-event-banner-preview" class="media-preview-grid"></div>
                        <input name="banner_image_url" value="{{ old('banner_image_url', $event->banner_image_url) }}" placeholder="أو رابط البانر" class="w-full rounded-2xl border-slate-200">
                        @if($event->banner_image_url)
                            <div class="overflow-hidden rounded-[1.5rem] border border-slate-200">
                                <img src="{{ $event->banner_image_url }}" alt="Banner" class="h-36 w-full object-cover">
                                <label class="flex items-center gap-2 px-4 py-3 text-sm font-bold text-rose-600">
                                    <input type="checkbox" name="remove_banner_image" value="1">
                                    حذف البنر الحالي
                                </label>
                            </div>
                        @endif
                    </div>
                    <input name="video_url" value="{{ old('video_url', $event->video_url) }}" placeholder="رابط الفيديو" class="w-full rounded-2xl border-slate-200">
                    <input name="map_url" value="{{ old('map_url', $event->map_url) }}" placeholder="رابط خرائط Google" class="w-full rounded-2xl border-slate-200">
                    <input name="meta_title" value="{{ old('meta_title', $event->meta_title) }}" placeholder="Meta title" class="w-full rounded-2xl border-slate-200">
                    <input name="meta_description" value="{{ old('meta_description', $event->meta_description) }}" placeholder="Meta description" class="w-full rounded-2xl border-slate-200 md:col-span-2">
                </div>
                <div class="space-y-4">
                    <label class="media-uploader" data-media-upload data-preview-target="organizer-event-gallery-preview" data-placeholder="اسحب صور المعرض هنا أو اضغط للاختيار">
                        <input type="file" name="gallery_files[]" multiple accept="image/*" class="sr-only">
                        <div class="space-y-2 text-center">
                            <p class="text-sm font-black text-slate-800">اسحب صور المعرض هنا أو اضغط للاختيار</p>
                            <p class="text-xs text-slate-500" data-media-text>يمكنك اختيار أكثر من صورة</p>
                        </div>
                    </label>
                    <div id="organizer-event-gallery-preview" class="media-preview-grid"></div>
                    <textarea name="gallery_images" rows="5" class="w-full rounded-2xl border-slate-200" placeholder="روابط إضافية - كل رابط صورة في سطر">{{ $galleryValue }}</textarea>
                    @if(! empty($existingGalleryImages))
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($existingGalleryImages as $image)
                                <label class="overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white">
                                    <img src="{{ $image }}" alt="Gallery image" class="h-28 w-full object-cover">
                                    <div class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-slate-700">
                                        <input type="checkbox" name="existing_gallery_images[]" value="{{ $image }}" checked>
                                        الإبقاء على الصورة
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-black">أنواع التذاكر</h2>
            <div class="mt-6 space-y-5">
                @foreach($ticketRows as $index => $ticket)
                    <div class="rounded-[1.5rem] border border-slate-200 p-5">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <input name="tickets[{{ $index }}][name]" value="{{ $ticket['name'] ?? '' }}" placeholder="اسم التذكرة" class="rounded-2xl border-slate-200">
                            <select name="tickets[{{ $index }}][type]" class="rounded-2xl border-slate-200">
                                @foreach($ticketTypes as $type)
                                    <option value="{{ $type }}" @selected(($ticket['type'] ?? 'regular') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="0.01" name="tickets[{{ $index }}][price]" value="{{ $ticket['price'] ?? '' }}" placeholder="السعر" class="rounded-2xl border-slate-200">
                            <input type="number" step="0.01" name="tickets[{{ $index }}][price_before_discount]" value="{{ $ticket['price_before_discount'] ?? '' }}" placeholder="قبل الخصم" class="rounded-2xl border-slate-200">
                            <input type="number" name="tickets[{{ $index }}][quantity]" value="{{ $ticket['quantity'] ?? '' }}" placeholder="الكمية" class="rounded-2xl border-slate-200">
                            <input type="number" name="tickets[{{ $index }}][purchase_limit_per_user]" value="{{ $ticket['purchase_limit_per_user'] ?? '' }}" placeholder="حد الشراء" class="rounded-2xl border-slate-200">
                            <input type="color" name="tickets[{{ $index }}][label_color]" value="{{ $ticket['label_color'] ?? '#7c3aed' }}" class="h-[52px] w-full rounded-2xl border border-slate-200 px-2 py-2">
                            <input type="number" name="tickets[{{ $index }}][sort_order]" value="{{ $ticket['sort_order'] ?? ($index + 1) }}" placeholder="الترتيب" class="rounded-2xl border-slate-200">
                            <select name="tickets[{{ $index }}][status]" class="rounded-2xl border-slate-200">
                                @foreach(['active', 'inactive', 'sold_out'] as $status)
                                    <option value="{{ $status }}" @selected(($ticket['status'] ?? 'active') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <input type="datetime-local" name="tickets[{{ $index }}][starts_at]" value="{{ $ticket['starts_at'] ?? '' }}" class="rounded-2xl border-slate-200">
                            <input type="datetime-local" name="tickets[{{ $index }}][ends_at]" value="{{ $ticket['ends_at'] ?? '' }}" class="rounded-2xl border-slate-200">
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <textarea name="tickets[{{ $index }}][description]" rows="3" placeholder="وصف التذكرة" class="w-full rounded-2xl border-slate-200">{{ $ticket['description'] ?? '' }}</textarea>
                            <textarea name="tickets[{{ $index }}][features]" rows="3" placeholder="المميزات - كل ميزة في سطر" class="w-full rounded-2xl border-slate-200">{{ $ticket['features'] ?? '' }}</textarea>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="tickets[{{ $index }}][is_active]" value="1" @checked($ticket['is_active'] ?? true)> مفعلة</label>
                            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="tickets[{{ $index }}][is_hidden]" value="1" @checked($ticket['is_hidden'] ?? false)> مخفية</label>
                            <label class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold"><input type="checkbox" name="tickets[{{ $index }}][uses_qr]" value="1" @checked($ticket['uses_qr'] ?? true)> QR / Barcode</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('organizer.events.index') }}" class="rounded-full border border-slate-200 px-6 py-3 font-bold text-slate-700">رجوع</a>
            <button class="rounded-full bg-slate-900 px-8 py-3 font-bold text-white">حفظ الفعالية</button>
        </div>
    </form>
</section>
@endsection
