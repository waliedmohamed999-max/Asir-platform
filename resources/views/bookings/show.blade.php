@extends('layouts.app')

@push('styles')
    <style>
        .ticket-page {
            max-width: 1120px;
            margin-inline: auto;
        }

        .ticket-summary-card,
        .ticket-info-card,
        .ticket-item-card {
            border: 1px solid #e5e7eb;
            border-radius: 28px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
        }

        .ticket-item-card {
            overflow: hidden;
            position: relative;
        }

        .ticket-item-header {
            background: linear-gradient(135deg, #0f172a 0%, #2e1065 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .ticket-item-header::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.16), transparent 34%),
                radial-gradient(circle at bottom right, rgba(168,85,247,.20), transparent 34%);
            pointer-events: none;
        }

        .ticket-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .ticket-badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .ticket-kv {
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            background: #fff;
            padding: 1rem 1.1rem;
        }

        .ticket-kv-label {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .ticket-kv-value {
            margin-top: 0.4rem;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.6;
            word-break: break-word;
        }

        .ticket-code-box,
        .ticket-qr-box,
        .ticket-barcode-box {
            border-radius: 24px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .ticket-perforation {
            position: relative;
        }

        .ticket-perforation::before,
        .ticket-perforation::after {
            content: "";
            position: absolute;
            top: 2.75rem;
            width: 28px;
            height: 28px;
            border-radius: 9999px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            z-index: 3;
        }

        .ticket-perforation::before {
            inset-inline-start: -14px;
        }

        .ticket-perforation::after {
            inset-inline-end: -14px;
        }

        .ticket-perforation-line {
            position: absolute;
            top: 3.6rem;
            inset-inline: 0;
            border-top: 2px dashed #d7ddea;
            z-index: 1;
        }

        .ticket-qr-target > img,
        .ticket-qr-target > canvas {
            margin-inline: auto;
        }

        .ticket-barcode-box {
            overflow: hidden;
        }

        .ticket-barcode-box svg {
            display: block;
            width: 100%;
            height: 78px;
        }

        .ticket-note {
            border-radius: 22px;
            background: #f8fafc;
            color: #475569;
        }

        .ticket-watermark {
            position: absolute;
            inset-inline-end: 1.5rem;
            inset-block-end: 1.5rem;
            font-size: 3.6rem;
            font-weight: 900;
            color: rgba(148, 163, 184, 0.08);
            pointer-events: none;
            user-select: none;
            line-height: 1;
        }

        .ticket-stub {
            border-radius: 22px;
            border: 1px dashed #d7ddea;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        @media print {
            header,
            footer,
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .ticket-summary-card,
            .ticket-info-card,
            .ticket-item-card {
                box-shadow: none !important;
            }

            .ticket-watermark {
                color: rgba(15, 23, 42, 0.06) !important;
            }
        }
    </style>
@endpush

@section('content')
<section class="ticket-page px-4 py-10">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4 no-print">
        <div>
            <span class="ticket-badge ticket-badge-success">تم تأكيد الحجز</span>
            <h1 class="mt-4 text-4xl font-black text-slate-950">تذكرتك جاهزة الآن</h1>
            <p class="mt-3 max-w-2xl text-base leading-8 text-slate-500">
                يمكنك الاحتفاظ بهذه الصفحة أو تنزيل التذكرة كملف PDF أو صورة، ثم إبرازها عند الدخول إلى الفعالية.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('bookings.index') }}" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">العودة إلى تذاكري</a>
            <button type="button" id="download-ticket-png" class="rounded-full border border-sky-200 bg-sky-50 px-5 py-3 text-sm font-bold text-sky-700">تحميل PNG</button>
            <a href="{{ route('bookings.pdf', $booking) }}" class="rounded-full border border-violet-200 bg-violet-50 px-5 py-3 text-sm font-bold text-violet-700">تنزيل PDF</a>
            <button onclick="window.print()" class="rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white">طباعة التذكرة</button>
        </div>
    </div>

    <div id="ticket-export-area" class="space-y-6">
        <div class="ticket-summary-card p-6 md:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-400">{{ $booking->reference }}</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-950">{{ $booking->event->title }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">
                        {{ $booking->event->city?->name ?? 'غير محددة' }}
                        •
                        {{ $booking->event->category?->name ?? 'فعاليات' }}
                        •
                        {{ $booking->event->venue_name ?: 'المكان يحدد لاحقاً' }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="ticket-kv min-w-[160px]">
                        <div class="ticket-kv-label">حالة الدفع</div>
                        <div class="ticket-kv-value">{{ strtoupper($booking->payment_status) }}</div>
                    </div>
                    <div class="ticket-kv min-w-[160px]">
                        <div class="ticket-kv-label">الإجمالي</div>
                        <div class="ticket-kv-value">{{ number_format($booking->total_amount, 2) }} ر.س</div>
                    </div>
                    <div class="ticket-kv min-w-[160px]">
                        <div class="ticket-kv-label">تاريخ الحجز</div>
                        <div class="ticket-kv-value">{{ $booking->booking_date?->translatedFormat('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
            <div class="space-y-6">
                @foreach($booking->items as $item)
                    <article class="ticket-item-card">
                        <div class="ticket-item-header p-6 md:p-8">
                            <div class="relative flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-white/70">تذكرة دخول رسمية</p>
                                    <h3 class="mt-3 text-3xl font-black">{{ $item->ticket_name }}</h3>
                                    <p class="mt-3 text-sm leading-7 text-white/70">
                                        {{ $booking->event->title }}
                                        •
                                        {{ $item->attendee_date?->translatedFormat('l، d M Y') }}
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[1.5rem] bg-white/10 px-5 py-4">
                                        <p class="text-xs text-white/60">القيمة</p>
                                        <p class="mt-2 text-2xl font-black">{{ number_format($item->line_total, 2) }} ر.س</p>
                                    </div>
                                    <div class="rounded-[1.5rem] bg-white/10 px-5 py-4">
                                        <p class="text-xs text-white/60">الكمية</p>
                                        <p class="mt-2 text-2xl font-black">{{ $item->quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ticket-perforation relative p-6 md:p-8">
                            <div class="ticket-perforation-line"></div>

                            <div class="grid gap-6 xl:grid-cols-[250px_1fr]">
                                <div class="space-y-4">
                                    <div class="ticket-stub p-5">
                                        <p class="ticket-kv-label">مقص التذكرة</p>
                                        <div class="mt-4 grid gap-4">
                                            <div class="ticket-kv">
                                                <div class="ticket-kv-label">الكمية</div>
                                                <div class="ticket-kv-value">{{ $item->quantity }}</div>
                                            </div>
                                            <div class="ticket-kv">
                                                <div class="ticket-kv-label">القيمة</div>
                                                <div class="ticket-kv-value">{{ number_format($item->line_total, 2) }} ر.س</div>
                                            </div>
                                            <div class="ticket-kv">
                                                <div class="ticket-kv-label">مرجع التذكرة</div>
                                                <div class="ticket-kv-value">{{ $booking->reference }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ticket-code-box p-5">
                                        <p class="ticket-kv-label">كود التذكرة</p>
                                        <p class="mt-3 break-all font-mono text-sm font-bold text-slate-800">{{ strtoupper($item->qr_token) }}</p>
                                    </div>

                                    <div class="ticket-qr-box p-5 text-center">
                                        <p class="ticket-kv-label">QR Code</p>
                                        <div class="ticket-qr-target mt-4 flex min-h-[150px] items-center justify-center" data-qr-code="{{ $item->qr_token }}"></div>
                                        <p class="mt-3 text-xs text-slate-400">استخدمه عند بوابة الدخول للتحقق السريع من التذكرة.</p>
                                    </div>
                                </div>

                                <div class="relative space-y-5">
                                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                        <div class="ticket-kv">
                                            <div class="ticket-kv-label">مرجع الحجز</div>
                                            <div class="ticket-kv-value">{{ $booking->reference }}</div>
                                        </div>
                                        <div class="ticket-kv">
                                            <div class="ticket-kv-label">البريد الإلكتروني</div>
                                            <div class="ticket-kv-value">{{ $booking->customer_email }}</div>
                                        </div>
                                        <div class="ticket-kv">
                                            <div class="ticket-kv-label">الجوال</div>
                                            <div class="ticket-kv-value">{{ $booking->customer_phone ?: 'غير متوفر' }}</div>
                                        </div>
                                        <div class="ticket-kv">
                                            <div class="ticket-kv-label">المدينة</div>
                                            <div class="ticket-kv-value">{{ $booking->event->city?->name ?? 'غير محددة' }}</div>
                                        </div>
                                        <div class="ticket-kv">
                                            <div class="ticket-kv-label">الفئة</div>
                                            <div class="ticket-kv-value">{{ $booking->event->category?->name ?? 'غير محددة' }}</div>
                                        </div>
                                        <div class="ticket-kv">
                                            <div class="ticket-kv-label">المكان</div>
                                            <div class="ticket-kv-value">{{ $booking->event->venue_name ?: 'غير محدد' }}</div>
                                        </div>
                                    </div>

                                    <div class="ticket-barcode-box p-5">
                                        <p class="ticket-kv-label">Barcode</p>
                                        <div class="mt-4">
                                            {!! \App\Support\Code39Barcode::svg($item->qr_token, 78) !!}
                                        </div>
                                    </div>

                                    <div class="ticket-note p-5 text-sm leading-8">
                                        يرجى إبراز هذه التذكرة عند الدخول. إذا كانت الكمية أكبر من 1 فسيتم التحقق من هذا الكود وفق عدد التذاكر المحجوزة داخل نفس البند.
                                    </div>

                                    <div class="ticket-watermark">Aseer</div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="space-y-6 no-print">
                <div class="ticket-info-card p-6">
                    <h2 class="text-2xl font-black text-slate-950">ملخص الدفع</h2>
                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center justify-between"><span class="text-slate-500">قبل الخصم</span><span class="font-black text-slate-950">{{ number_format($booking->subtotal_amount, 2) }} ر.س</span></div>
                        <div class="flex items-center justify-between"><span class="text-slate-500">الخصم</span><span class="font-black text-slate-950">{{ number_format($booking->discount_amount, 2) }} ر.س</span></div>
                        <div class="flex items-center justify-between border-t border-slate-200 pt-4 text-base"><span class="font-bold text-slate-500">الإجمالي</span><span class="text-xl font-black text-slate-950">{{ number_format($booking->total_amount, 2) }} ر.س</span></div>
                    </div>
                </div>

                <div class="ticket-info-card p-6">
                    <h2 class="text-2xl font-black text-slate-950">معلومات العملية</h2>
                    <div class="mt-6 space-y-4 text-sm">
                        <div>
                            <p class="ticket-kv-label">بوابة الدفع</p>
                            <p class="ticket-kv-value">{{ strtoupper($booking->latestPayment?->gateway ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="ticket-kv-label">حالة العملية</p>
                            <p class="ticket-kv-value">{{ $booking->latestPayment?->status ?? 'pending' }}</p>
                        </div>
                        <div>
                            <p class="ticket-kv-label">رقم العملية</p>
                            <p class="ticket-kv-value">{{ $booking->latestPayment?->transaction_reference ?? 'غير متوفر' }}</p>
                        </div>
                        <div>
                            <p class="ticket-kv-label">عدد البنود</p>
                            <p class="ticket-kv-value">{{ $booking->items->count() }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-qr-code]').forEach(function (element) {
                const value = element.getAttribute('data-qr-code');

                if (!value || typeof QRCode === 'undefined') {
                    return;
                }

                element.innerHTML = '';
                new QRCode(element, {
                    text: value,
                    width: 132,
                    height: 132,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            });

            const pngButton = document.getElementById('download-ticket-png');
            const exportArea = document.getElementById('ticket-export-area');

            if (pngButton && exportArea && typeof html2canvas !== 'undefined') {
                pngButton.addEventListener('click', function () {
                    pngButton.disabled = true;
                    pngButton.textContent = 'جارٍ تجهيز الصورة...';

                    html2canvas(exportArea, {
                        backgroundColor: '#f8fafc',
                        scale: 2,
                        useCORS: true,
                    }).then(function (canvas) {
                        const link = document.createElement('a');
                        link.download = 'ticket-{{ $booking->reference }}.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    }).finally(function () {
                        pngButton.disabled = false;
                        pngButton.textContent = 'تحميل PNG';
                    });
                });
            }
        });
    </script>
@endpush
