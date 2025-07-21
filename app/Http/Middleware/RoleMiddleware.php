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
            return redirect()->route('login')->withErrors(['access' => 'Sessione scaduta. Effettua nuovamente il login.']);
        }

        $user = Auth::user();

        // Verifica se l'utente è attivo
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['access' => 'Account disattivato.']);
        }

        // Verifica il ruolo
        if ($user->role !== $role) {

            \Log::warning('Unauthorized access attempt:', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'attempted_role' => $role,
                'ip' => $request->ip(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['access' => 'Accesso non autorizzato.'])
                ->withHeaders($this->getBasicSecurityHeaders());
        }

        return $next($request);
    }

    /**
     * Header di sicurezza basilari per progetto universitario
     */
    private function getBasicSecurityHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];
    }
}