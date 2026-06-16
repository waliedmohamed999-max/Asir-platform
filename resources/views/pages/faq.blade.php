@extends('layouts.app')

@section('content')
<section class="section-shell py-14">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-black text-slate-900">الأسئلة الشائعة</h1>
            <p class="mt-3 text-lg text-slate-600">إجابات سريعة لأكثر الأسئلة شيوعاً حول الحجز والتذاكر والدفع.</p>
        </div>

        <div class="space-y-8">
            @forelse($faqs as $category => $items)
                <div class="soft-card p-8">
                    <h2 class="text-2xl font-black text-slate-900">{{ $category }}</h2>
                    <div class="mt-6 space-y-4">
                        @foreach($items as $faq)
                            <div class="rounded-[1.5rem] border border-slate-100 p-5">
                                <h3 class="text-xl font-black text-slate-900">{{ $faq->question }}</h3>
                                <p class="mt-3 whitespace-pre-line text-[15px] leading-8 text-slate-600">{{ $faq->answer }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="soft-card p-8 text-center text-slate-500">لا توجد أسئلة شائعة منشورة حالياً.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
