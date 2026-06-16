<div class="glass sticky top-24 overflow-hidden rounded-[2rem] border border-violet-100 p-0 shadow-2xl shadow-violet-900/10">
    <div class="bg-gradient-to-br from-slate-950 via-violet-950 to-fuchsia-700 p-6 text-white">
    <div class="flex items-start justify-between gap-4">
        <div>
                <p class="text-sm font-bold text-white/65">احجز الآن</p>
            <h2 class="text-2xl font-black">التذاكر والأسعار</h2>
        </div>
            <div class="rounded-2xl bg-white/10 px-4 py-2 text-center">
                <p class="text-xs text-white/60">يبدأ من</p>
                <p class="text-xl font-black">{{ $event->starting_price }} ر.س</p>
            </div>
        </div>
    </div>

    <div class="p-6">
    @guest
        <div class="mt-6 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800">
            يرجى <a href="{{ route('login') }}" class="font-bold underline">تسجيل الدخول</a> لإتمام الحجز.
        </div>
    @endguest

    <form method="GET" action="{{ route('bookings.create', $event) }}" class="mt-6 space-y-5">
        <div>
            <label class="mb-2 block text-sm font-bold">اختر التاريخ</label>
            <input type="date" name="booking_date" wire:model.live="booking_date" min="{{ optional($event->start_date)->format('Y-m-d') }}" class="w-full rounded-2xl border-slate-200">
        </div>

        <div class="space-y-3">
            @forelse($event->tickets as $ticket)
                @php
                    $discount = $ticket->price_before_discount && $ticket->price_before_discount > $ticket->price
                        ? round((($ticket->price_before_discount - $ticket->price) / $ticket->price_before_discount) * 100)
                        : 0;
                    $isAvailable = $ticket->isReservableForDate(1, $booking_date);
                @endphp
                <div class="overflow-hidden rounded-[1.5rem] border border-violet-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-gradient-to-l from-violet-50 to-white p-4">
                    <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-slate-950">{{ $ticket->name }}</h3>
                                    <span class="rounded-full bg-violet-100 px-2.5 py-1 text-[11px] font-black text-violet-700">{{ strtoupper($ticket->type) }}</span>
                                    @if($discount > 0)
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-black text-rose-700">خصم {{ $discount }}%</span>
                                    @endif
                                </div>
                            <p class="text-sm text-slate-500">
                                {{ $ticket->description ?: 'متاح للحجز الآن' }}
                                @if($ticket->ends_at)
                                    • متاح حتى {{ $ticket->ends_at->translatedFormat('d M, h:i A') }}
                                @endif
                            </p>
                        </div>
                        <div class="text-left">
                                @if($ticket->price_before_discount)
                                    <p class="text-xs font-bold text-slate-400 line-through">{{ number_format($ticket->price_before_discount, 2) }} ر.س</p>
                                @endif
                                <p class="text-xl font-black text-violet-700">{{ number_format($ticket->price, 2) }} ر.س</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        @if(is_array($ticket->features) && count($ticket->features))
                            <div class="mb-4 flex flex-wrap gap-2">
                                @foreach(array_slice($ticket->features, 0, 4) as $feature)
                                    <span class="rounded-full border border-slate-200 px-3 py-1 text-xs font-bold text-slate-600">{{ $feature }}</span>
                                @endforeach
                            </div>
                        @endif
                    <div class="flex items-center justify-between">
                        <input type="hidden" name="quantities[{{ $ticket->id }}]" value="{{ $quantities[$ticket->id] ?? 0 }}">
                        <div class="flex items-center gap-3">
                                <button type="button" wire:click="increment({{ $ticket->id }})" @disabled(! $isAvailable) class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xl text-white disabled:bg-slate-300">+</button>
                            <span class="min-w-8 text-center font-black">{{ $quantities[$ticket->id] ?? 0 }}</span>
                            <button type="button" wire:click="decrement({{ $ticket->id }})" class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-xl">-</button>
                        </div>
                            <span class="rounded-full {{ $isAvailable ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} px-3 py-1 text-xs font-black">
                                {{ $isAvailable ? $ticket->remaining_quantity.' متبقي' : 'غير متاح' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-5 text-center text-sm text-slate-500">
                    لا توجد تذاكر متاحة للبيع حالياً لهذه الفعالية.
                </div>
            @endforelse
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold">كود الخصم</label>
            <input type="text" name="promo_code" wire:model.live="promo_code" placeholder="FARAH10" class="w-full rounded-2xl border-slate-200">
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold">طريقة الدفع</label>
            <select name="payment_method" wire:model.live="payment_method" class="w-full rounded-2xl border-slate-200">
                <option value="stripe">Stripe</option>
                <option value="paypal">PayPal</option>
                <option value="mada">مدى</option>
                <option value="cash">تأكيد تجريبي</option>
            </select>
        </div>

        <div class="rounded-[1.5rem] bg-slate-900 p-5 text-white">
            <div class="flex items-center justify-between text-sm">
                <span>الإجمالي قبل الخصم</span>
                <span>{{ number_format($this->subtotal, 2) }} ر.س</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-sm text-cyan-200">
                <span>الخصم</span>
                <span>{{ number_format($this->discount, 2) }} ر.س</span>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4">
                <span class="text-lg font-bold">الإجمالي النهائي</span>
                <span class="text-2xl font-black">{{ number_format($this->total, 2) }} ر.س</span>
            </div>
        </div>

        <button @guest disabled @endguest @disabled($event->tickets->isEmpty()) class="w-full rounded-2xl bg-cyan-500 px-4 py-4 text-base font-black text-white disabled:cursor-not-allowed disabled:bg-slate-300">
            احجز الآن
        </button>
    </form>

    <div class="mt-6 rounded-[1.5rem] bg-cyan-50 p-4 text-sm text-cyan-900">
        بعد المتابعة ستظهر لك شاشة مراجعة البيانات وتأكيد الحجز قبل إصدار التذكرة.
    </div>
    </div>
</div>
