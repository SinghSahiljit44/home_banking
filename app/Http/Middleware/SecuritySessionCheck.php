<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecuritySessionCheck
{
    /**
     * Handle an incoming request 
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se è un tentativo di login, pulisci i flag
        if ($this->isLoginAttempt($request)) {
            $this->clearSecurityFlags($request);
            return $next($request);
        }
        
        // Controlla logout forzato
        if (session()->has('forced_logout_redirect')) {
            $timestamp = session('forced_logout_timestamp', 0);
            
            // Se sono passati più di 5 minuti, rimuovi il flag
            if (now()->timestamp - $timestamp > 300) {
                $this->clearSecurityFlags($request);
            } else {
                // Se sta tentando di accedere a pagine protette
                if ($this->isProtectedRoute($request) && !Auth::check()) {
                    $this->clearSecurityFlags($request);
                    
                    return redirect()->route('login')
                        ->withErrors(['security' => 'Non è possibile tornare indietro per motivi di sicurezza.'])
                        ->withHeaders($this->getBasicHeaders());
                }
            }
        }

        return $next($request);
    }

    /**
     * Verifica se è un tentativo di login
     */
    private function isLoginAttempt(Request $request): bool
    {
        $uri = $request->path();
        return $request->isMethod('POST') && (
            $uri === 'login-cliente' || 
            $uri === 'login-lavoratore' ||
            str_contains($uri, 'login')
        );
    }

    /**
     * Pulisce i flag di sicurezza essenziali
     */
    private function clearSecurityFlags(Request $request): void
    {
        // Solo i flag essenziali per un progetto universitario
        $flagsToRemove = [
            'forced_logout_redirect',
            'forced_logout_reason', 
            'forced_logout_timestamp',
        ];
        
        foreach ($flagsToRemove as $flag) {
            $request->session()->forget($flag);
        }
    }

    /**
     * Verifica se la rotta è protetta
     */
    private function isProtectedRoute(Request $request): bool
    {
        $protectedPaths = ['dashboard', 'admin', 'employee', 'client'];
        $currentPath = $request->path();
        
        foreach ($protectedPaths as $path) {
            if ($currentPath === $path || str_starts_with($currentPath, $path . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Header basilari
     */
    private function getBasicHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];
    }
}