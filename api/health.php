<?php

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'ok' => true,
    'app' => 'Asir Platform',
    'runtime' => PHP_VERSION,
    'time' => gmdate('c'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
