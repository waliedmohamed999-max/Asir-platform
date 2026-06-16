<?php

foreach ([
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'LOG_CHANNEL' => 'stderr',
    'LOG_STACK' => 'stderr',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'cookie',
    'QUEUE_CONNECTION' => 'sync',
    'VIEW_COMPILED_PATH' => '/tmp/laravel-views',
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

if (! is_dir('/tmp/laravel-views')) {
    mkdir('/tmp/laravel-views', 0777, true);
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

require __DIR__ . '/../public/index.php';
