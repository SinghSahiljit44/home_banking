<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registra i middleware personalizzati
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
            'security.session' => \App\Http\Middleware\SecuritySessionCheck::class,
        ]);

        // Applica i middleware di sicurezza a tutte le route web
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
            \App\Http\Middleware\SecuritySessionCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();