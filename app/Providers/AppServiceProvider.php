<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TransactionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TransactionService::class, function ($app) {
            return new TransactionService();
        });
        
    }
}