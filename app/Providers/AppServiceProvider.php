<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

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
        Http::macro('api', function () {
            return Http::withHeaders([
                'Accept' => 'application/json',
                'x-client-id' => env('API_CLIENT_ID'),
                'x-api-key' => env('API_KEY'),
            ])->baseUrl(config('services.api.base_uri'));
        });
    }
}
