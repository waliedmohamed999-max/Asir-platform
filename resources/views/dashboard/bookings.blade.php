@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">تذاكري وحجوزاتي</h1>
            <p class="mt-2 text-slate-500">كل الحجوزات المؤكدة تظهر هنا مع إمكانية فتح التذكرة وطباعتها لاحقاً.</p>
        </div>
        <a href="{{ route('home') }}" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">استكشاف فعاليات جديدة</a>
    </div>
    <div class="mt-8 space-y-4">
        @foreach($bookings as $booking)
            <a href="{{ route('bookings.show', $booking) }}" class="block rounded-[2rem] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">{{ $booking->reference }}</p>
                        <h2 class="mt-2 text-2xl font-black">{{ $booking->event->title }}</h2>
                        <p class="mt-2 text-sm text-slate-500">افتح الحجز لعرض التذكرة والباركود الخاص بها</p>
                    </div>
                    <div class="text-left">
                        <p class="text-sm text-slate-500">{{ $booking->booking_date->translatedFormat('d M Y') }}</p>
                        <p class="mt-1 text-xl font-black">{{ number_format($booking->total_amount, 2) }} ر.س</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $bookings->links() }}</div>
</section>
@endsection
