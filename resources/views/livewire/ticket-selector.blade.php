<div class="space-y-6">
    <div class="space-y-4">
        <div>
            <label class="block text-slate-700">نوع التذكرة</label>
            <select wire:model="ticketTypeId" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 p-4 focus:border-brand-500 focus:outline-none">
                @foreach($ticketTypes as $ticket)
                    <option value="{{ $ticket->id }}">{{ $ticket->name }} - {{ number_format($ticket->price) }} ر.س</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-slate-700">الكمية</label>
                <input wire:model="quantity" type="number" min="1" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 p-4 focus:border-brand-500 focus:outline-none" />
            </div>
            <div>
                <label class="block text-slate-700">كود الخصم</label>
                <input wire:model="promoCode" type="text" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 p-4 focus:border-brand-500 focus:outline-none" />
            </div>
        </div>

        <div class="rounded-3xl bg-slate-100 p-5">
            <div class="flex items-center justify-between text-slate-700">
                <span>المجموع</span>
                <span>{{ number_format($total) }} ر.س</span>
            </div>
            <div class="flex items-center justify-between text-slate-700">
                <span>الخصم</span>
                <span>{{ number_format($discount) }} ر.س</span>
            </div>
            <div class="mt-4 flex items-center justify-between text-xl font-semibold text-brand-700">
                <span>السعر النهائي</span>
                <span>{{ number_format($final) }} ر.س</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.book', $event) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="ticket_type_id" value="{{ $ticketTypeId }}" />
        <input type="hidden" name="quantity" value="{{ $quantity }}" />
        <input type="hidden" name="promo_code" value="{{ $promoCode }}" />
        <button type="submit" class="btn-primary w-full">احجز الآن</button>
    </form>
</div>
