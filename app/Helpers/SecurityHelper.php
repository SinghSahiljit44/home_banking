<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityHelper
{
    /**
     * Esegue logout di sicurezza
     */
    public static function forceSecureLogout(Request $request, string $reason = 'security', string $message = null): void
    {
        $user = Auth::user();
        
        // Log semplificato
        Log::warning('Security logout:', [
            'user_id' => $user?->id,
            'reason' => $reason,
            'ip' => $request->ip(),
        ]);

        // Logout se autenticato
        if (Auth::check()) {
            Auth::logout();
        }
        
        // Pulisci sessione
        static::cleanSession($request);
        
        // Imposta flag basilari
        static::setSecurityFlags($request, $reason, $message);
    }
    
    /**
     * Pulisce la sessione
     */
    public static function cleanSession(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    
    /**
     * Imposta flag di sicurezza essenziali
     */
    public static function setSecurityFlags(Request $request, string $reason, string $message = null): void
    {
        session()->put('forced_logout_redirect', true);
        session()->put('forced_logout_reason', $reason);
        session()->put('forced_logout_timestamp', now()->timestamp);
        
        if ($message) {
            session()->put('security_message', $message);
        }
        
        session()->save();
    }
    
    /**
     * Header di sicurezza basilari
     */
    public static function getSecurityHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];
    }
    
    /**
     * Verifica se un percorso è protetto
     */
    public static function isProtectedRoute(string $path): bool
    {
        $protectedPaths = ['dashboard', 'admin', 'employee', 'client'];
        
        foreach ($protectedPaths as $protectedPath) {
            if ($path === $protectedPath || str_starts_with($path, $protectedPath . '/')) {
                return true;
            }
        }
        
        return false;
    }
}