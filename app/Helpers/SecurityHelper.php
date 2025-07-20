<?php
// Crea questo file: app/Helpers/SecurityHelper.php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityHelper
{
    /**
     * Esegue un logout di sicurezza completo
     */
    public static function forceSecureLogout(Request $request, string $reason = 'security', string $message = null): void
    {
        $user = Auth::user();
        
        // Log del logout forzato
        Log::warning('Security logout executed:', [
            'user_id' => $user?->id,
            'user_name' => $user?->full_name,
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'referer' => $request->header('referer'),
            'timestamp' => now()->toISOString(),
        ]);

        // Logout dell'utente se autenticato
        if (Auth::check()) {
            Auth::logout();
        }
        
        // Pulisci completamente la sessione
        static::cleanSession($request);
        
        // Imposta flag per prevenire back button
        static::setSecurityFlags($request, $reason, $message);
    }
    
    /**
     * Pulisce completamente la sessione
     */
    public static function cleanSession(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();
        
        if ($request->hasSession()) {
            $request->session()->migrate(true);
        }
    }
    
    /**
     * Imposta flag di sicurezza nella sessione
     */
    public static function setSecurityFlags(Request $request, string $reason, string $message = null): void
    {
        session()->put('forced_logout_redirect', true);
        session()->put('forced_logout_reason', $reason);
        session()->put('forced_logout_timestamp', now()->timestamp);
        session()->put('unauthorized_access_attempt', true);
        
        if ($message) {
            session()->put('security_message', $message);
        }
        
        session()->save();
    }
    
    /**
     * Verifica se è un tentativo di back button
     */
    public static function isBackButtonAttempt(Request $request): bool
    {
        $referer = $request->header('referer');
        
        // Controlla referer
        if ($referer && (
            str_contains($referer, '/login') || 
            str_contains($referer, '/login-cliente') || 
            str_contains($referer, '/login-lavoratore')
        )) {
            return true;
        }
        
        // Controlla flag di sessione
        if (session()->has('forced_logout_redirect') || 
            session()->has('unauthorized_access_attempt') ||
            session()->has('back_button_blocked')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Ottieni header di sicurezza anti-cache
     */
    public static function getSecurityHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate, private',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Clear-Site-Data' => '"cache", "storage", "executionContexts"',
            'X-Robots-Tag' => 'noindex, nofollow, nosnippet, noarchive',
            'X-Security-Policy' => 'strict',
            'Vary' => 'Cookie, Authorization'
        ];
    }
    
    /**
     * Determina la route di login appropriata
     */
    public static function getLoginRoute(Request $request): string
    {
        $url = $request->fullUrl();
        $path = $request->path();
        
        if (str_contains($url, '/admin') || str_contains($path, 'admin') || 
            str_contains($url, '/employee') || str_contains($path, 'employee') ||
            str_contains($url, 'dashboard-admin') || str_contains($url, 'dashboard-employee')) {
            return 'login.lavoratore';
        }
        
        if (str_contains($url, '/client') || str_contains($path, 'client') ||
            str_contains($url, 'dashboard-cliente')) {
            return 'login.cliente';
        }
        
        return 'login';
    }
    
    /**
     * Verifica se un percorso è protetto
     */
    public static function isProtectedRoute(string $path): bool
    {
        $protectedPaths = [
            'dashboard',
            'dashboard-admin',
            'dashboard-employee', 
            'dashboard-cliente',
            'admin',
            'employee',
            'client'
        ];
        
        foreach ($protectedPaths as $protectedPath) {
            if ($path === $protectedPath || str_starts_with($path, $protectedPath . '/')) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Registra tentativo di accesso non autorizzato
     */
    public static function logUnauthorizedAttempt(Request $request, array $context = []): void
    {
        Log::warning('Unauthorized access attempt detected:', array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'referer' => $request->header('referer'),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context));
    }
}