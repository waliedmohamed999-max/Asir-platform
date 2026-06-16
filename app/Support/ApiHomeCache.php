<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ApiHomeCache
{
    public static function clear(): void
    {
        foreach (['ar', 'en'] as $lang) {
            Cache::forget("api:v1:smart-home:$lang");
        }
    }
}
