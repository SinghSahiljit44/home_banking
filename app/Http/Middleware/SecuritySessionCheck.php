<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecuritySessionCheck
{
    /**
     * Handle an incoming request.
     * Questo middleware controlla tentativi di accesso dopo logout forzato
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Controlla se c'è un flag di logout forzato nella sessione
        if (session()->has('forced_logout_redirect')) {
            $reason = session('forced_logout_reason', 'general');
            $timestamp = session('forced_logout_timestamp', 0);
            
            // Se sono passati più di 5 minuti, rimuovi il flag
            if (now()->timestamp - $timestamp > 300) {
                session()->forget(['forced_logout_redirect', 'forced_logout_reason', 'forced_logout_timestamp']);
            } else {
                // Se l'utente sta tentando di accedere a una pagina protetta
                if ($this->isProtectedRoute($request) && !Auth::check()) {
                    
                    // Log del tentativo di back button
                    \Log::warning('Back button attempt after forced logout:', [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'attempted_url' => $request->fullUrl(),
                        'referer' => $request->header('referer'),
                        'logout_reason' => $reason,
                        'logout_timestamp' => $timestamp,
                        'time_since_logout' => now()->timestamp - $timestamp,
                    ]);

                    // Pulisci completamente la sessione
                    session()->flush();
                    session()->regenerate(true);
                    
                    // Reindirizza con messaggio specifico
                    $message = $this->getMessageForReason($reason);
                    
                    return redirect()->route('login')
                        ->withErrors(['security' => $message])
                        ->withHeaders($this->getSecurityHeaders());
                }
            }
        }

        // Controlla se l'utente sta tentando di accedere a pagine protette dopo essere stato reindirizzato
        if ($this->isUnauthorizedAccess($request)) {
            // Forza logout e pulisci sessione
            $this->forceLogoutAndCleanSession($request);
            
            return redirect()->route('login')
                ->withErrors(['access' => 'Accesso negato. Non è possibile tornare indietro dopo un logout per sicurezza.'])
                ->withHeaders($this->getSecurityHeaders());
        }

        return $next($request);
    }

    /**
     * Verifica se è un accesso non autorizzato
     */
    private function isUnauthorizedAccess(Request $request): bool
    {
        // Se l'utente non è autenticato ma sta tentando di accedere a pagine protette
        if (!Auth::check() && $this->isProtectedRoute($request)) {
            // Controlla se il referer è la pagina di login (possibile back button)
            $referer = $request->header('referer');
            if ($referer && (
                str_contains($referer, '/login') || 
                str_contains($referer, '/login-cliente') || 
                str_contains($referer, '/login-lavoratore')
            )) {
                return true;
            }

            // Controlla se ci sono cookie o sessioni residue che indicano un logout recente
            if (session()->has('_previous') && $this->wasRecentlyLoggedOut($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se c'è stato un logout recente
     */
    private function wasRecentlyLoggedOut(Request $request): bool
    {
        // Controlla se ci sono tracce di un logout recente nella sessione
        $previousUrl = session('_previous.url', '');
        
        // Se l'URL precedente era una dashboard o pagina protetta
        return str_contains($previousUrl, 'dashboard') || 
               str_contains($previousUrl, 'admin') || 
               str_contains($previousUrl, 'employee') || 
               str_contains($previousUrl, 'client');
    }

    /**
     * Forza logout e pulisce completamente la sessione
     */
    private function forceLogoutAndCleanSession(Request $request): void
    {
        if (Auth::check()) {
            \Log::info('Forced logout due to unauthorized access attempt:', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'attempted_url' => $request->fullUrl(),
            ]);
            
            Auth::logout();
        }
        
        // Pulisci completamente la sessione
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();
        
        // Forza la rigenerazione dell'ID di sessione
        if ($request->hasSession()) {
            $request->session()->migrate(true);
        }

        // Imposta flag per prevenire ulteriori tentativi
        session()->put('access_denied_redirect', true);
        session()->put('access_denied_timestamp', now()->timestamp);
        session()->save();
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
     * Ottieni il messaggio appropriato in base al motivo del logout
     */
    private function getMessageForReason(string $reason): string
    {
        $messages = [
            'unauthorized_access' => 'Accesso negato. Non è possibile tornare indietro dopo un logout per accesso non autorizzato.',
            'account_disabled' => 'Accesso negato. Account disattivato.',
            'general' => 'Accesso negato. Non è possibile tornare indietro dopo un logout per sicurezza.'
        ];

        return $messages[$reason] ?? $messages['general'];
    }

    /**
     * Header di sicurezza per prevenire cache e back button
     */
    private function getSecurityHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate, private',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Clear-Site-Data' => '"cache", "storage"',
            'X-Robots-Tag' => 'noindex, nofollow, nosnippet, noarchive',
            'X-Unauthorized-Access' => 'denied'
        ];
    }
}