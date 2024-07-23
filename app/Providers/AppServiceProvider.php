<?php

namespace App\Providers;

use App\Models\BaseTelegramRequestModel;
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
        // $this->app->bind(Client::class, function (Application $app) {
        //     if ($app->environment('testing')) {
        //         return new \Mockery::mock(\GuzzleHttp\Client::class); 
        //     } else {
        //         return new Client(); // Use the default Http client for other environments
        //     }
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
