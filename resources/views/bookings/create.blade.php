@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-violet-600">خطوة 2 من 3</p>
            <h1 class="mt-2 text-4xl font-black">مراجعة الحجز وتأكيد البيانات</h1>
            <p class="mt-3 text-slate-500">راجع التذاكر المختارة، أدخل بيانات التواصل، ثم أكد الحجز لإصدار التذاكر مباشرة.</p>
        </div>
        <a href="{{ route('events.show', $event) }}" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">العودة للفعالية</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_.85fr]">
        <form method="POST" action="{{ route('bookings.store', $event) }}" class="space-y-6">
            @csrf

            <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-black">بيانات الحجز</h2>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold">الاسم الكامل</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" class="w-full rounded-2xl border-slate-200" required>
                        @error('customer_name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">البريد الإلكتروني</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}" class="w-full rounded-2xl border-slate-200" required>
                        @error('customer_email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">رقم الجوال</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone) }}" class="w-full rounded-2xl border-slate-200">
                        @error('customer_phone') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold">طريقة الدفع</label>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 font-bold text-slate-800">
                            {{ strtoupper($validated['payment_method']) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black">تفاصيل التذاكر</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $event->title }} • {{ $event->city?->name }} • {{ $event->venue_name }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        {{ \Carbon\Carbon::parse($validated['booking_date'])->translatedFormat('l، d M Y') }}
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach($checkout['items'] as $line)
                        <div class="rounded-[1.5rem] border border-slate-100 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-black">{{ $line['ticket']->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $line['ticket']->description ?: 'تذكرة صالحة للدخول في التاريخ المحدد' }}</p>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm text-slate-500">الكمية</p>
                                    <p class="text-2xl font-black">{{ $line['quantity'] }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                <p class="text-sm text-slate-500">{{ number_format($line['ticket']->price, 2) }} ر.س لكل تذكرة</p>
                                <p class="text-lg font-black">{{ number_format($line['line_total'], 2) }} ر.س</p>
                            </div>
                        </div>
                        <input type="hidden" name="quantities[{{ $line['ticket']->id }}]" value="{{ $line['quantity'] }}">
                    @endforeach
                </div>

                <input type="hidden" name="booking_date" value="{{ $validated['booking_date'] }}">
                <input type="hidden" name="payment_method" value="{{ $validated['payment_method'] }}">
                <input type="hidden" name="promo_code" value="{{ $validated['promo_code'] ?? '' }}">
            </div>

            <div class="rounded-[2rem] bg-slate-900 p-6 text-white shadow-xl">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black">تأكيد الحجز</h2>
                        <p class="mt-2 text-sm text-slate-300">بالضغط على الزر سيتم إصدار التذكرة فوراً وربطها بكود خاص لكل بند.</p>
                    </div>
                    <button class="rounded-2xl bg-cyan-500 px-6 py-4 text-lg font-black text-white transition hover:bg-cyan-400">
                        تأكيد الحجز وإصدار التذكرة
                    </button>
                </div>
            </div>
        </form>

        <aside class="space-y-6">
            <div class="overflow-hidden rounded-[2rem] bg-white shadow-sm">
                <img src="{{ $event->primary_image_url }}" alt="{{ $event->title }}" class="h-56 w-full object-cover">
            </div>

            <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-black">ملخص الدفع</h2>
                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">الإجمالي قبل الخصم</span>
                        <span class="font-bold">{{ number_format($checkout['subtotal'], 2) }} ر.س</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">الخصم</span>
                        <span class="font-bold text-emerald-600">- {{ number_format($checkout['discount'], 2) }} ر.س</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-xl">
                        <span class="font-bold">الإجمالي النهائي</span>
                        <span class="font-black">{{ number_format($checkout['total'], 2) }} ر.س</span>
                    </div>
                </div>

                <div class="mt-6 rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">
                    @if(!empty($validated['promo_code']))
                        <p>تم تطبيق الكود: <span class="font-black text-slate-900">{{ strtoupper($validated['promo_code']) }}</span></p>
                    @else
                        <p>لا يوجد كود خصم مطبق على هذا الحجز.</p>
                    @endif
                </div>
            </div>

            <div class="rounded-[2rem] bg-violet-50 p-6 text-sm text-violet-900 shadow-sm">
                <h3 class="text-lg font-black">بعد التأكيد مباشرة</h3>
                <ul class="mt-4 space-y-2 leading-7">
                    <li>يتم إنشاء مرجع حجز فريد.</li>
                    <li>يتم إصدار كود لكل تذكرة.</li>
                    <li>تظهر صفحة التذكرة مع Barcode مخصص لكل بند.</li>
                    <li>يتم إرسال نسخة إلى بريدك الإلكتروني إذا كان البريد مفعلًا.</li>
                </ul>
            </div>
        </aside>
    </div>
</section>
@endsection
