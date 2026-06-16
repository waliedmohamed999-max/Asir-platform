@extends('layouts.app')

@section('content')
<section class="section-shell py-14 md:py-20">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1.05fr_.95fr] lg:items-stretch">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-violet-700 via-violet-600 to-fuchsia-600 p-8 text-white shadow-[0_30px_80px_rgba(91,33,182,.22)] md:p-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,.22),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,.12),transparent_30%)]"></div>
            <div class="relative">
                <img src="{{ asset('branding/aseer-logo.png') }}" alt="شعار منصة عسير" class="h-20 w-auto rounded-2xl bg-white/95 p-2 shadow-lg md:h-24">
                <h1 class="mt-8 text-3xl font-black leading-tight md:text-5xl">دخول سريع إلى لوحة التحكم والحجوزات</h1>
                <p class="mt-5 max-w-xl text-base leading-8 text-violet-50 md:text-lg">
                    سجّل الدخول لإدارة الفعاليات، متابعة التذاكر، الوصول إلى لوحة الأدمن أو المنظم، ومراجعة الحجوزات من مكان واحد.
                </p>

                <div class="mt-10 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/20 bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm text-violet-100">الأدمن</p>
                        <p class="mt-2 font-black">إدارة المحتوى والتقارير</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/20 bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm text-violet-100">المنظم</p>
                        <p class="mt-2 font-black">متابعة الفعاليات والحجوزات</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/20 bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm text-violet-100">المستخدم</p>
                        <p class="mt-2 font-black">التذاكر والحساب الشخصي</p>
                    </div>
                </div>

                <div class="mt-10 rounded-[1.5rem] border border-white/20 bg-white/10 p-5 text-sm backdrop-blur">
                    <p class="font-black">حسابات تجريبية سريعة</p>
                    <div class="mt-3 space-y-2 text-violet-50">
                        <p><span class="font-bold">Admin:</span> admin@farah.sa / password</p>
                        <p><span class="font-bold">Organizer:</span> organizer@farah.sa / password</p>
                        <p><span class="font-bold">User:</span> customer@farah.sa / password</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-7 shadow-[0_20px_60px_rgba(15,23,42,.08)] md:p-10">
            <div class="text-right">
                <p class="text-sm font-bold text-violet-700">منصة عسير</p>
                <h2 class="mt-2 text-3xl font-black text-slate-900">تسجيل الدخول</h2>
                <p class="mt-3 text-sm leading-7 text-slate-500">أدخل بياناتك للانتقال تلقائياً إلى الداشبورد المناسب حسب نوع الحساب.</p>
            </div>

            @if($errors->any())
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100" required autofocus>
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-bold text-slate-700">كلمة المرور</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-base focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100" required>
                </div>

                <div class="flex items-center justify-between gap-4 text-sm">
                    <label class="flex items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-violet-700 focus:ring-violet-200">
                        تذكرني
                    </label>
                    <a href="{{ route('register') }}" class="font-bold text-violet-700">إنشاء حساب جديد</a>
                </div>

                <button class="w-full rounded-2xl bg-slate-900 px-4 py-3.5 text-base font-black text-white transition hover:bg-violet-700">دخول إلى الحساب</button>
            </form>
        </div>
    </div>
</section>
@endsection
