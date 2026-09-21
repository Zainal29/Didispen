<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WhatsappMessageService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // public function register(): void
    // {
    //     //
    // }

    /**
     * Bootstrap any application services.
     */
    // public function boot(): void
    // {
    //     //
    // }
    public function register(): void
    {
        $this->app->singleton(WhatsappMessageService::class, function ($app) {
            return new WhatsappMessageService();
        });
    }
}
