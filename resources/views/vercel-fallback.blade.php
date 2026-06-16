<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>منصة عسير</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Tahoma, Arial, sans-serif;
            background: radial-gradient(circle at 20% 0%, #f3e8ff, transparent 32%), #f7f8fc;
            color: #091127;
        }
        .wrap {
            width: min(1040px, calc(100% - 32px));
            margin: 0 auto;
            padding: 56px 0;
        }
        .card {
            border: 1px solid #e6e9f2;
            border-radius: 32px;
            background: rgba(255,255,255,.92);
            padding: 36px;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .08);
        }
        .logo { width: 120px; height: auto; }
        h1 { margin: 28px 0 10px; font-size: clamp(34px, 6vw, 70px); line-height: 1.05; }
        p { color: #64748b; font-size: 18px; line-height: 1.9; max-width: 760px; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        a {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 24px;
            font-weight: 800;
            text-decoration: none;
        }
        .primary { background: #111827; color: white; }
        .ghost { border: 1px solid #d8deea; color: #111827; }
        .note {
            margin-top: 24px;
            border-radius: 20px;
            background: #f8fafc;
            padding: 16px;
            color: #64748b;
            font-size: 13px;
            direction: ltr;
            text-align: left;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <img class="logo" src="/branding/aseer-logo.png" alt="منصة عسير">
            <h1>منصة عسير جاهزة للنشر</h1>
            <p>
                تم تشغيل نسخة Vercel بنجاح. التطبيق الثابت متاح الآن، ولتشغيل الداشبورد والبيانات الحية بالكامل
                أضف متغيرات قاعدة البيانات من إعدادات Vercel ثم أعد النشر.
            </p>
            <div class="actions">
                <a class="primary" href="/mobile-app/">فتح تطبيق Flutter Web</a>
                <a class="ghost" href="/health">فحص السيرفر</a>
            </div>
            <div class="note">{{ $reason }}</div>
        </section>
    </main>
</body>
</html>
