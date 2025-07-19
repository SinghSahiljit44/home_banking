<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Aggiungi header per prevenire il caching e il back button
        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                       ->header('Pragma', 'no-cache')
                       ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT')
                       ->header('X-Frame-Options', 'DENY')
                       ->header('X-Content-Type-Options', 'nosniff');
    }
}