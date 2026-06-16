<?php

namespace App\Providers;

use App\Models\Page;
use App\Services\Admin\SettingsService;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, fn () => new SettingsService());
    }

    public function boot(): void
    {
        Paginator::useTailwind();
        Carbon::setLocale(config('app.locale'));

        View::composer(['layouts.admin'], function ($view) {
            $view->with('appSettings', app(SettingsService::class)->publicSettings());
        });

        View::composer(['layouts.app'], function ($view) {
            $footerPages = Page::query()
                ->where('is_active', true)
                ->where('show_in_footer', true)
                ->orderBy('footer_group')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('footer_group');

            $view->with('appSettings', app(SettingsService::class)->publicSettings());
            $view->with('footerPages', $footerPages);
        });
    }
}
