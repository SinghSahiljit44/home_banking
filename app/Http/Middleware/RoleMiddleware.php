<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return $this->redirectToLogin($request, 'Sessione scaduta. Effettua nuovamente il login.');
        }

        $user = Auth::user();

        // Verifica se l'utente è attivo
        if (!$user->is_active) {
            $this->secureLogout($request, 'account_disabled');
            return $this->redirectToLogin($request, 'Account disattivato.');
        }

        // Verifica il ruolo
        if ($user->role !== $role) {
            // Log dell'tentativo di accesso non autorizzato
            \Log::warning('Unauthorized access attempt:', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'attempted_role' => $role,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ]);

            // Logout sicuro con flag di accesso non autorizzato
            $this->secureLogout($request, 'unauthorized_access');
            
            return $this->redirectToLogin($request, 'Accesso non autorizzato. Per sicurezza sei stato disconnesso.');
        }

        return $next($request);
    }

    /**
     * Esegue un logout sicuro con invalidazione completa della sessione
     */
    private function secureLogout(Request $request, string $reason = 'general'): void
    {
        $userId = Auth::id();
        $userName = Auth::user()->full_name ?? 'Unknown';
        
        // Log del logout forzato
        \Log::info('Forced logout executed:', [
            'user_id' => $userId,
            'user_name' => $userName,
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);

        // Logout dell'utente
        Auth::logout();
        
        // Invalida completamente la sessione
        $request->session()->invalidate();
        
        // Rigenera il token CSRF
        $request->session()->regenerateToken();
        
        // Flush di tutti i dati della sessione
        $request->session()->flush();
        
        // Forza la rigenerazione dell'ID di sessione
        if ($request->hasSession()) {
            $request->session()->migrate(true);
        }

        // Imposta flag per identificare il logout forzato nel prossimo accesso
        session()->put('forced_logout_redirect', true);
        session()->put('forced_logout_reason', $reason);
        session()->put('forced_logout_timestamp', now()->timestamp);
        session()->put('unauthorized_access_attempt', true);
        session()->save();
    }

    /**
     * Reindirizza al login con messaggio appropriato e header anti-cache
     */
    private function redirectToLogin(Request $request, string $message): Response
    {
        // Determina la pagina di login corretta in base al tentativo di accesso
        $loginRoute = $this->determineLoginRoute($request);
        
        $response = redirect()->route($loginRoute)->withErrors(['access' => $message]);
        
        // Rimuovi eventuali cookie di remember_me
        if ($request->hasCookie(Auth::getRecallerName())) {
            $response = $response->withCookie(cookie()->forget(Auth::getRecallerName()));
        }

        // Aggiungi header per prevenire il back button e la cache
        return $response->withHeaders($this->getSecurityHeaders());
    }

    /**
     * Determina quale route di login usare in base alla richiesta
     */
    private function determineLoginRoute(Request $request): string
    {
        $url = $request->fullUrl();
        
        // Se stava tentando di accedere a una sezione admin o employee
        if (str_contains($url, '/admin') || str_contains($url, '/employee') || str_contains($url, 'dashboard-admin') || str_contains($url, 'dashboard-employee')) {
            return 'login.lavoratore';
        }
        
        // Se stava tentando di accedere a una sezione client
        if (str_contains($url, '/client') || str_contains($url, 'dashboard-cliente')) {
            return 'login.cliente';
        }
        
        // Default
        return 'login';
    }

    /**
     * Header di sicurezza completi
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
            'X-Unauthorized-Access' => 'blocked'
        ];
    }
}