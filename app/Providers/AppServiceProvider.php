<?php

namespace App\Providers;

use App\Classes\ModerationSettings;
use App\Services\TelegramBotService;
use App\Classes\BaseCommand;
use App\Models\TelegramRequestModelBuilder;
use App\Services\BaseBotCommandCore;
use App\Services\TelegramMiddlewareService;
use App\Classes\PrivateChatCommandCore;
use App\Classes\Menu;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\HttpClientException;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramBotService::class, function ($app) {
            return new TelegramBotService();
        });
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
