<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventBackAfterLogout
{
    /**
     * Handle an incoming request.
     * Questo middleware previene specificamente il back button dopo logout forzato
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se l'utente non è autenticato
        if (!Auth::check()) {
            
            // Controlla se sta tentando di accedere a una pagina protetta
            if ($this->isProtectedRoute($request)) {
                
                // Controlla se proviene da una pagina di login (possibile back button)
                if ($this->isBackButtonAttempt($request)) {
                    
                    \Log::warning('Back button attempt detected:', [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'attempted_url' => $request->fullUrl(),
                        'referer' => $request->header('referer'),
                        'session_data' => [
                            'has_forced_logout' => session()->has('forced_logout_redirect'),
                            'has_unauthorized_attempt' => session()->has('unauthorized_access_attempt'),
                        ]
                    ]);
                    
                    // Pulisci completamente la sessione
                    $this->cleanSession($request);
                    
                    // Determina la pagina di login appropriata
                    $loginRoute = $this->determineLoginRoute($request);
                    
                    return redirect()->route('login')
                        ->withErrors(['security' => 'Accesso negato. Non è possibile tornare indietro dopo essere stati disconnessi per sicurezza.'])
                        ->withHeaders($this->getAntiBackHeaders());
                }
                
                // Se non è un back button ma è comunque una pagina protetta, reindirizza normalmente
                return redirect()->route('login')
                    ->withErrors(['access' => 'Accesso richiesto.'])
                    ->withHeaders($this->getAntiBackHeaders());
            }
        }

        return $next($request);
    }

    /**
     * Verifica se è un tentativo di back button
     */
    private function isBackButtonAttempt(Request $request): bool
    {
        $referer = $request->header('referer');
        
        // Controlla se il referer è una pagina di login
        if ($referer && (
            str_contains($referer, '/login') || 
            str_contains($referer, '/login-cliente') || 
            str_contains($referer, '/login-lavoratore')
        )) {
            return true;
        }
        
        // Controlla se ci sono flag di logout forzato nella sessione
        if (session()->has('forced_logout_redirect') || session()->has('unauthorized_access_attempt')) {
            return true;
        }
        
        // Controlla se l'URL precedente nella sessione era protetto
        $previousUrl = session('_previous.url', '');
        if ($this->isProtectedUrl($previousUrl)) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se la rotta è protetta
     */
    private function isProtectedRoute(Request $request): bool
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

        $currentPath = $request->path();
        
        foreach ($protectedPaths as $path) {
            if ($currentPath === $path || str_starts_with($currentPath, $path . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se un URL è protetto
     */
    private function isProtectedUrl(string $url): bool
    {
        $protectedPaths = [
            'dashboard',
            'admin',
            'employee',
            'client'
        ];
        
        foreach ($protectedPaths as $path) {
            if (str_contains($url, $path)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Pulisce completamente la sessione
     */
    private function cleanSession(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();
        
        if ($request->hasSession()) {
            $request->session()->migrate(true);
        }
        
        // Imposta un nuovo flag per tracciare questo tentativo
        session()->put('back_button_blocked', true);
        session()->put('back_button_blocked_timestamp', now()->timestamp);
        session()->save();
    }

    /**
     * Header anti-back specifici
     */
    private function getAntiBackHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate, private, no-transform',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Clear-Site-Data' => '"cache", "storage", "executionContexts"',
            'X-Robots-Tag' => 'noindex, nofollow, nosnippet, noarchive',
            'X-Back-Button' => 'blocked',
            'Vary' => 'Cookie, Authorization'
        ];
    }
}