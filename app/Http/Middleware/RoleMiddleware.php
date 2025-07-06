<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        //Recupera l'utente dalla tabella users oppure dalla cache 
        $user = Auth::user();

        // Verifica se l'utente è attivo
        if (!$user->is_active) {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Account disattivato.']);
        }

        // Verifica il ruolo usando il campo 'role' diretto
        if ($user->role !== $role) {
            // Logout se sta tentando di accedere a un'area non autorizzata
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Accesso non autorizzato per questo ruolo.']);
        }

        return $next($request);
    }
}