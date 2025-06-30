<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();
        
        // Verifica se l'utente è attivo
        if (!$user->is_active) {
            auth()->logout();
            return redirect('/login')->withErrors(['access' => 'Account disattivato.']);
        }
        
        // Verifica se il ruolo dell'utente è in quelli permessi
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        abort(403, 'Accesso non autorizzato');
    }
}