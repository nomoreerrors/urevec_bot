<?php

namespace App\Providers;

use App\Classes\ModerationSettings;
use App\Classes\BaseCommand;
use App\Models\TelegramRequestModelBuilder;
use App\Services\BaseBotCommandCore;
use App\Services\PrivateChatCommandCore;
use App\Services\TelegramMiddlewareService;
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
        // $this->app->when(PrivateChatCommandService::class)
        //     ->needs(ReplyInterface::class)
        //     ->give(
        //         function (Application $app) {
        //             return $app->make(ModerationSettings::class);
        //         }
        //     );

        // $this->app->when(BotCommandService::class)
        //     ->needs(ReplyInterface::class)
        //     ->give(
        //         function (Application $app) {
        //             return $app->make(ModerationSettings::class);
        //         }
        //     );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
