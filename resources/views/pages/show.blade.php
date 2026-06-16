@extends('layouts.app')

@section('content')
<section class="section-shell py-14">
    <div class="mx-auto max-w-5xl">
        <div class="soft-card p-8 lg:p-12">
            <p class="text-sm text-slate-500">{{ $page->slug }}</p>
            <h1 class="mt-3 text-4xl font-black text-slate-900">{{ $page->title }}</h1>
            @if($page->excerpt)
                <p class="mt-4 text-lg leading-8 text-slate-600">{{ $page->excerpt }}</p>
            @endif
            <div class="mt-8 whitespace-pre-line text-[16px] leading-9 text-slate-700">{{ $page->body }}</div>
        </div>
    </div>
</section>
@endsection
