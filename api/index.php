<?php

function env_value(string $key): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return $value === false || $value === '' ? null : $value;
}

function send_vercel_fallback(string $reason, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');

    $safeReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>منصة عسير</title>
    <style>
        body{margin:0;min-height:100vh;font-family:Tahoma,Arial,sans-serif;background:#f7f8fc;color:#091127}
        .wrap{width:min(1040px,calc(100% - 32px));margin:0 auto;padding:56px 0}
        .card{border:1px solid #e6e9f2;border-radius:24px;background:#fff;padding:36px;box-shadow:0 24px 64px rgba(15,23,42,.08)}
        h1{margin:0 0 12px;font-size:clamp(32px,6vw,64px);line-height:1.1}
        p{color:#64748b;font-size:18px;line-height:1.9;max-width:760px}
        .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:28px}
        .event{border:1px solid #e5e9f2;border-radius:16px;padding:18px;background:#f8fafc}
        .event b{display:block;margin-bottom:8px;font-size:18px}
        .actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}
        a{display:inline-flex;min-height:48px;align-items:center;justify-content:center;border-radius:999px;padding:0 24px;font-weight:800;text-decoration:none}
        .primary{background:#111827;color:white}
        .ghost{border:1px solid #d8deea;color:#111827}
        .note{margin-top:24px;border-radius:16px;background:#f8fafc;padding:16px;color:#64748b;font-size:13px;direction:ltr;text-align:left;overflow-wrap:anywhere}
        @media(max-width:820px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <h1>اكتشف فعاليات وتجارب منصة عسير</h1>
            <p>واجهة الموقع العامة تعمل الآن. استعرض الفعاليات، افتح التطبيق، أو ادخل إلى لوحة التحكم التجريبية.</p>
            <div class="grid">
                <div class="event"><b>Cyan Waterpark - جدة</b><span>مغامرات مائية عائلية من 60 ر.س</span></div>
                <div class="event"><b>حفلات الموسم</b><span>أمسيات موسيقية وعروض حية</span></div>
                <div class="event"><b>كأس الرياض</b><span>فعاليات رياضية وتجارب جماهيرية</span></div>
            </div>
            <div class="actions">
                <a class="primary" href="/login">تسجيل الدخول</a>
                <a class="ghost" href="/admin">لوحة التحكم</a>
                <a class="ghost" href="/mobile-app/">تطبيق Flutter</a>
            </div>
            <div class="note">$safeReason</div>
        </section>
    </main>
</body>
</html>
HTML;

    exit;
}

function set_env_value(string $key, string $value): void
{
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

function runtime_temp_dir(): string
{
    return DIRECTORY_SEPARATOR === '\\'
        ? dirname(__DIR__) . '/storage/vercel-runtime'
        : '/tmp';
}

function configure_demo_database(): void
{
    if (env_value('DB_URL') !== null || env_value('DB_HOST') !== null) {
        return;
    }

    $source = __DIR__ . '/../database/vercel.sqlite';
    $target = runtime_temp_dir() . '/aseer-vercel.sqlite';

    if (! is_file($source)) {
        return;
    }

    if (! is_dir(dirname($target))) {
        @mkdir(dirname($target), 0777, true);
    }

    if (! is_file($target) || filesize($target) !== filesize($source)) {
        copy($source, $target);
    }

    set_env_value('DB_CONNECTION', 'sqlite');
    set_env_value('DB_DATABASE', $target);
}

foreach ([
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'LOG_CHANNEL' => 'stderr',
    'LOG_STACK' => 'stderr',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'file',
    'SESSION_SECURE_COOKIE' => 'true',
    'SESSION_SAME_SITE' => 'lax',
    'QUEUE_CONNECTION' => 'sync',
    'VIEW_COMPILED_PATH' => runtime_temp_dir() . '/laravel-views',
    'LARAVEL_STORAGE_PATH' => runtime_temp_dir() . '/laravel-storage',
] as $key => $value) {
    if (empty($_ENV[$key]) && empty($_SERVER[$key]) && getenv($key) === false) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

if (empty($_ENV['APP_KEY']) && empty($_SERVER['APP_KEY']) && getenv('APP_KEY') === false) {
    $seed = getenv('VERCEL_GIT_COMMIT_SHA') ?: 'aseer-platform-vercel-fallback-key';
    $key = 'base64:' . base64_encode(hash('sha256', $seed, true));
    putenv('APP_KEY=' . $key);
    $_ENV['APP_KEY'] = $key;
    $_SERVER['APP_KEY'] = $key;
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

configure_demo_database();

foreach ([
    env_value('LARAVEL_STORAGE_PATH') . '/app',
    env_value('LARAVEL_STORAGE_PATH') . '/framework/cache/data',
    env_value('LARAVEL_STORAGE_PATH') . '/framework/sessions',
    env_value('LARAVEL_STORAGE_PATH') . '/framework/views',
    env_value('LARAVEL_STORAGE_PATH') . '/logs',
    env_value('VIEW_COMPILED_PATH'),
] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

try {
    require __DIR__ . '/../public/index.php';
} catch (Throwable $exception) {
    send_vercel_fallback($exception->getMessage(), 500);
}
