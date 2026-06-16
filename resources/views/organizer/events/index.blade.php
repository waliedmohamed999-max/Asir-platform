@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">فعالياتي</h1>
            <p class="mt-2 text-sm text-slate-500">أدر فعالياتك وتذاكرك ومواعيد البيع من حساب المنظم.</p>
        </div>
        <a href="{{ route('organizer.events.create') }}" class="rounded-full bg-slate-900 px-5 py-3 text-white">فعالية جديدة</a>
    </div>
    <div class="mt-8 grid gap-4 xl:grid-cols-2">
        @forelse($events as $event)
            <div class="rounded-[2rem] bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $event->status }}</span>
                            @if($event->is_featured)
                                <span class="rounded-full bg-fuchsia-100 px-3 py-1 text-xs font-bold text-fuchsia-700">مميزة</span>
                            @endif
                        </div>
                        <h2 class="mt-3 text-2xl font-black">{{ $event->title }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $event->venue_name }} • {{ $event->start_date?->translatedFormat('d M Y - h:i A') }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $event->tickets->count() }} نوع تذكرة</p>
                    </div>
                    <a href="{{ route('organizer.events.edit', $event) }}" class="rounded-full border border-slate-200 px-4 py-2 font-bold">تعديل</a>
                </div>
            </div>
        @empty
            <div class="rounded-[2rem] bg-white p-8 text-center text-slate-500 shadow-sm xl:col-span-2">لا توجد فعاليات حتى الآن. أضف أول فعالية من حساب المنظم.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $events->links() }}</div>
</section>
@endsection
