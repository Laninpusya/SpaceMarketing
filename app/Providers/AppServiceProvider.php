<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramBotApi;
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
    public function boot()
    {
            
    }
}
