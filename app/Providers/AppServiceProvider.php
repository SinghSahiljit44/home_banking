<?php
// Aggiorna app/Providers/AppServiceProvider.php

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
        
        // Registra l'helper di sicurezza
        require_once app_path('Helpers/SecurityHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aggiungi macro per i redirect di sicurezza
        \Illuminate\Http\RedirectResponse::macro('withSecurityHeaders', function () {
            return $this->withHeaders(\App\Helpers\SecurityHelper::getSecurityHeaders());
        });
        
        // Macro per logout di sicurezza
        \Illuminate\Http\Request::macro('forceSecureLogout', function (string $reason = 'security', string $message = null) {
            \App\Helpers\SecurityHelper::forceSecureLogout($this, $reason, $message);
        });
    }
}