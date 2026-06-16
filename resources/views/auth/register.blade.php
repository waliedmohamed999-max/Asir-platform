@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-xl px-4 py-16">
    <div class="glass rounded-[2rem] p-8 shadow-lg">
        <h1 class="text-3xl font-black">إنشاء حساب جديد</h1>
        <form method="POST" class="mt-8 space-y-4">
            @csrf
            <input type="text" name="name" placeholder="الاسم الكامل" class="w-full rounded-2xl border-slate-200">
            <input type="email" name="email" placeholder="البريد الإلكتروني" class="w-full rounded-2xl border-slate-200">
            <input type="text" name="phone" placeholder="رقم الجوال" class="w-full rounded-2xl border-slate-200">
            <input type="password" name="password" placeholder="كلمة المرور" class="w-full rounded-2xl border-slate-200">
            <input type="password" name="password_confirmation" placeholder="تأكيد كلمة المرور" class="w-full rounded-2xl border-slate-200">
            <button class="w-full rounded-2xl bg-slate-900 px-4 py-3 font-bold text-white">إنشاء حساب</button>
        </form>
    </div>
</section>
@endsection
