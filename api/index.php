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
        .actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}
        a{display:inline-flex;min-height:48px;align-items:center;justify-content:center;border-radius:999px;padding:0 24px;font-weight:800;text-decoration:none}
        .primary{background:#111827;color:white}
        .ghost{border:1px solid #d8deea;color:#111827}
        .note{margin-top:24px;border-radius:16px;background:#f8fafc;padding:16px;color:#64748b;font-size:13px;direction:ltr;text-align:left;overflow-wrap:anywhere}
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <h1>منصة عسير تعمل على Vercel</h1>
            <p>تم تشغيل النشر بنجاح. لتفعيل لوحة التحكم والبيانات الحية بالكامل، أضف متغيرات قاعدة البيانات في إعدادات Vercel ثم أعد النشر.</p>
            <div class="actions">
                <a class="primary" href="/mobile-app/">فتح تطبيق Flutter Web</a>
                <a class="ghost" href="/health">فحص السيرفر</a>
            </div>
            <div class="note">$safeReason</div>
        </section>
    </main>
</body>
</html>
HTML;

    exit;
}

function send_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function has_database_config(): bool
{
    return env_value('DB_URL') !== null || env_value('DB_HOST') !== null;
}

function send_mock_api_response(string $path): void
{
    $event = [
        'id' => 1,
        'slug' => 'aseer-demo-event',
        'title' => 'منصة عسير جاهزة للتشغيل',
        'subtitle' => 'أضف بيانات قاعدة البيانات في Vercel لإظهار الفعاليات الحقيقية',
        'description' => 'هذا محتوى مؤقت يظهر فقط عند عدم ضبط قاعدة البيانات.',
        'image_url' => '/branding/aseer-logo.png',
        'banner_image_url' => '/branding/aseer-logo.png',
        'venue_name' => 'عسير',
        'starts_at' => gmdate('c', strtotime('+3 days')),
        'start_date' => gmdate('Y-m-d H:i:s', strtotime('+3 days')),
        'starting_price' => 0,
        'is_featured' => true,
        'category' => ['id' => 1, 'slug' => 'events', 'name' => 'فعاليات'],
        'city' => ['id' => 1, 'slug' => 'aseer', 'name' => 'عسير'],
        'tickets' => [],
    ];

    if ($path === '/api/v1/home') {
        send_json([
            'banners' => [[
                'title' => 'منصة عسير تعمل الآن',
                'subtitle' => 'النشر ناجح، وتفعيل البيانات يحتاج DB فقط',
                'badge' => 'Vercel',
                'image_url' => '/branding/aseer-logo.png',
                'hero_image_url' => '/branding/aseer-logo.png',
            ]],
            'quick_actions' => [],
            'trending_searches' => ['عسير', 'فعاليات', 'تذاكر'],
            'sections' => [
                'app_stories' => [],
                'events' => [$event],
                'recommended' => [$event],
                'trending' => [$event],
                'upcoming' => [$event],
                'featured_events' => [],
                'featured_tourism' => [],
                'today_cards' => [],
                'experience_cards' => [],
                'offers' => [],
                'services' => [],
                'places' => [],
                'most_requested' => [],
                'nearby' => [],
                'today' => [$event],
            ],
            'filters' => [
                'cities' => [$event['city']],
                'categories' => [$event['category']],
            ],
        ]);
    }

    if ($path === '/api/v1/events') {
        send_json(['data' => [$event], 'meta' => ['database_configured' => false]]);
    }

    if ($path === '/api/v1/resale-listings') {
        send_json(['data' => []]);
    }

    if (preg_match('#^/api/v1/(offers|services|venues|cities|categories|recommendations)$#', $path)) {
        send_json(['data' => []]);
    }

    send_json([
        'message' => 'Database environment variables are not configured on Vercel yet.',
        'required' => ['DB_HOST or DB_URL', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    ], 503);
}

foreach ([
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'LOG_CHANNEL' => 'stderr',
    'LOG_STACK' => 'stderr',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'cookie',
    'QUEUE_CONNECTION' => 'sync',
    'VIEW_COMPILED_PATH' => '/tmp/laravel-views',
    'LARAVEL_STORAGE_PATH' => '/tmp/laravel-storage',
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

if (str_starts_with($requestPath, '/api/v1') && ! has_database_config()) {
    send_mock_api_response($requestPath);
}

if ($requestPath === '/' && ! has_database_config()) {
    send_vercel_fallback('Missing Vercel database environment variables: DB_HOST or DB_URL.');
}

foreach ([
    '/tmp/laravel-storage/app',
    '/tmp/laravel-storage/framework/cache/data',
    '/tmp/laravel-storage/framework/sessions',
    '/tmp/laravel-storage/framework/views',
    '/tmp/laravel-storage/logs',
    '/tmp/laravel-views',
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

try {
    require __DIR__ . '/../public/index.php';
} catch (Throwable $exception) {
    send_vercel_fallback($exception->getMessage(), 500);
}
