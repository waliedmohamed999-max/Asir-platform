<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('farah:demo-report', function () {
    $this->comment('Farah demo platform is ready.');
})->purpose('Show a quick Farah platform status message.');
