<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>منصة عسير</title>
    <style>
        body { margin: 0; font-family: Tahoma, Arial, sans-serif; background: #f7f8fc; color: #091127; }
        .top { background: #08090d; color: #fff; }
        .wrap { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
        .nav { min-height: 72px; display: flex; align-items: center; gap: 18px; }
        .logo { height: 46px; width: auto; border-radius: 10px; background: #fff; }
        .brand { font-size: 22px; font-weight: 900; }
        .spacer { flex: 1; }
        a { color: inherit; text-decoration: none; }
        .pill { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border-radius: 999px; font-weight: 800; }
        .primary { background: #c8f000; color: #08090d; }
        .ghost { border: 1px solid rgba(255,255,255,.22); color: #fff; }
        .hero { padding: 64px 0 72px; }
        .hero h1 { max-width: 780px; margin: 0 0 18px; font-size: clamp(36px, 7vw, 76px); line-height: 1.05; }
        .hero p { max-width: 720px; margin: 0; color: #c9ced8; font-size: 20px; line-height: 1.9; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .section { padding: 38px 0; }
        .section h2 { margin: 0 0 18px; font-size: 30px; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .card { overflow: hidden; border: 1px solid #e5e9f2; border-radius: 18px; background: #fff; box-shadow: 0 16px 42px rgba(15,23,42,.08); }
        .media { height: 190px; display: grid; place-items: center; background: linear-gradient(135deg, #111827, #ff2d7a); color: #fff; font-size: 54px; }
        .body { padding: 18px; }
        .body h3 { margin: 0 0 8px; font-size: 20px; }
        .body p { margin: 0 0 14px; color: #64748b; line-height: 1.7; }
        .price { color: #0f766e; font-weight: 900; }
        .note { margin-top: 24px; border-radius: 14px; background: #eef2ff; padding: 14px; color: #475569; direction: ltr; text-align: left; overflow-wrap: anywhere; }
        @media (max-width: 820px) { .grid { grid-template-columns: 1fr; } .nav { flex-wrap: wrap; padding: 14px 0; } }
    </style>
</head>
<body>
    <header class="top">
        <div class="wrap">
            <nav class="nav">
                <img class="logo" src="/branding/aseer-logo.png" alt="منصة عسير">
                <div class="brand">منصة عسير</div>
                <div class="spacer"></div>
                <a class="pill ghost" href="/login">تسجيل الدخول</a>
                <a class="pill primary" href="/admin">لوحة التحكم</a>
            </nav>
            <section class="hero">
                <h1>اكتشف الفعاليات والتجارب في المملكة</h1>
                <p>واجهة الموقع العامة تعمل الآن مع نسخة عرض جاهزة. يمكنك استعراض الفعاليات، فتح تطبيق الجوال، أو دخول لوحة التحكم التجريبية.</p>
                <div class="actions">
                    <a class="pill primary" href="/events/cyan-waterpark-jeddah">احجز فعالية</a>
                    <a class="pill ghost" href="/mobile-app/">فتح تطبيق Flutter</a>
                    <a class="pill ghost" href="/health">فحص السيرفر</a>
                </div>
            </section>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="wrap">
                <h2>فعاليات مميزة</h2>
                <div class="grid">
                    <article class="card">
                        <div class="media">🎟</div>
                        <div class="body">
                            <h3>Cyan Waterpark - جدة</h3>
                            <p>مغامرات مائية عائلية وتجربة صيفية قابلة للحجز.</p>
                            <div class="price">من 60 ر.س</div>
                        </div>
                    </article>
                    <article class="card">
                        <div class="media">♪</div>
                        <div class="body">
                            <h3>حفلات الموسم</h3>
                            <p>أمسيات موسيقية وعروض حية ضمن تجربة عربية متكاملة.</p>
                            <div class="price">من 250 ر.س</div>
                        </div>
                    </article>
                    <article class="card">
                        <div class="media">🏆</div>
                        <div class="body">
                            <h3>كأس الرياض</h3>
                            <p>فعاليات رياضية وتجارب جماهيرية قابلة للإدارة من الداشبورد.</p>
                            <div class="price">من 80 ر.س</div>
                        </div>
                    </article>
                </div>
                <div class="note">{{ $reason ?? 'Demo public website fallback is active.' }}</div>
            </div>
        </section>
    </main>
</body>
</html>
