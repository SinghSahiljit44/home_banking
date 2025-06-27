<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use App\Services\OtpService;
use App\Services\TransactionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registra il custom login response
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
        
        // Registra i servizi personalizzati
        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService();
        });
        
        $this->app->singleton(TransactionService::class, function ($app) {
            return new TransactionService();
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