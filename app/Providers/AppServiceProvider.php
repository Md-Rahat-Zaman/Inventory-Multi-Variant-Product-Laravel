<?php

namespace App\Providers;

use App\Models\ShopSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view): void {
            $request = request();

            if (! $request->attributes->has('shopSettings')) {
                $request->attributes->set('shopSettings', ShopSetting::query()->find(1));
            }

            $view->with('shopSettings', $request->attributes->get('shopSettings'));
        });
    }
}
