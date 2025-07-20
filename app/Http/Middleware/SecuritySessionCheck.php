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
                    
                    return redirect('/login')
                        ->withErrors(['security' => $message])
                        ->withHeaders($this->getSecurityHeaders());
                }
            }
        }

        return $next($request);
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
            'X-Robots-Tag' => 'noindex, nofollow, nosnippet, noarchive'
        ];
    }
}