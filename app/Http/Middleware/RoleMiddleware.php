<?php
// app/Http/Middleware/RoleMiddleware.php

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
            return redirect('/login');
        }

        $user = Auth::user();

        // Verifica se l'utente è attivo
        if (!$user->is_active) {
            $this->secureLogout($request);
            return redirect('/login')->withErrors(['access' => 'Account disattivato.']);
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
            ]);

            // Logout sicuro con invalidazione completa della sessione
            $this->secureLogout($request);
            
            // Invalidate anche eventuali cookie di remember_me
            if ($request->hasCookie(Auth::getRecallerName())) {
                $response = redirect('/login')->withErrors([
                    'access' => 'Accesso non autorizzato. Per sicurezza sei stato disconnesso.'
                ]);
                
                return $response->withCookie(cookie()->forget(Auth::getRecallerName()));
            }

            return redirect('/login')->withErrors([
                'access' => 'Accesso non autorizzato. Per sicurezza sei stato disconnesso.'
            ]);
        }

        return $next($request);
    }

    /**
     * Esegue un logout sicuro con invalidazione completa della sessione
     */
    private function secureLogout(Request $request): void
    {
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
    }
}