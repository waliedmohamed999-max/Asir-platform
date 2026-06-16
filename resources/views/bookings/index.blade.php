@extends('layouts.app')

@section('title','حجوزاتي')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-semibold">حجوزاتي</h1>
    <div class="grid gap-6">
        @foreach($bookings as $booking)
            <div class="card p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">{{ $booking->event->title }}</h2>
                        <p class="mt-2 text-slate-500">تاريخ الحجز: {{ $booking->booking_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('bookings.show', $booking) }}" class="btn-primary">عرض التفاصيل</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
