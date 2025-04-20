<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Notifications\Notification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Cache::forget('settings');
        view()->composer('*', function ($view) {
            $view->with('site_name', Setting::get('site_name', 'Tên website'));
            $view->with('site_logo', Setting::get('site_logo', 'images/logo.png'));
        });

    }
}
