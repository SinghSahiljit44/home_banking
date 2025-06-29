<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class EmployeePermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // Admin ha sempre tutti i permessi
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Solo employee possono usare questo middleware
        if (!$user->isEmployee()) {
            abort(403, 'Accesso non autorizzato');
        }

        // Verifica permessi specifici
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission, $request)) {
                abort(403, "Non hai il permesso: {$permission}");
            }
        }

        return $next($request);
    }

    /**
     * Verifica se l'employee ha il permesso specificato
     */
    private function hasPermission(User $employee, string $permission, Request $request): bool
    {
        switch ($permission) {
            case 'manage_assigned_clients':
                return $this->canManageRequestedClient($employee, $request);
                
            case 'view_assigned_transactions':
                return $this->canViewRequestedTransactions($employee, $request);
                
            case 'make_transfers_for_assigned':
                return $this->canMakeTransfersForRequested($employee, $request);
                
            case 'reset_assigned_passwords':
                return $this->canResetRequestedPassword($employee, $request);
                
            default:
                return false;
        }
    }

    /**
     * Verifica se può gestire il cliente richiesto
     */
    private function canManageRequestedClient(User $employee, Request $request): bool
    {
        $clientId = $this->extractClientId($request);
        
        if (!$clientId) {
            return true; // Se non c'è un cliente specifico, permetti (per liste generali)
        }

        $client = User::find($clientId);
        return $client && $employee->canManageClient($client);
    }

    /**
     * Verifica se può vedere le transazioni richieste
     */
    private function canViewRequestedTransactions(User $employee, Request $request): bool
    {
        $clientId = $this->extractClientId($request);
        
        if (!$clientId) {
            return true; // Per viste generali delle proprie transazioni
        }

        $client = User::find($clientId);
        return $client && $employee->canViewClientTransactions($client);
    }

    /**
     * Verifica se può fare bonifici per il cliente richiesto
     */
    private function canMakeTransfersForRequested(User $employee, Request $request): bool
    {
        $clientId = $this->extractClientId($request);
        
        if (!$clientId) {
            return false; // Deve sempre specificare per quale cliente
        }

        $client = User::find($clientId);
        return $client && $employee->canMakeTransfersForClient($client);
    }

    /**
     * Verifica se può resettare la password del cliente richiesto
     */
    private function canResetRequestedPassword(User $employee, Request $request): bool
    {
        $clientId = $this->extractClientId($request);
        
        if (!$clientId) {
            return false; // Deve sempre specificare per quale cliente
        }

        $client = User::find($clientId);
        return $client && $employee->canManageClient($client);
    }

    /**
     * Estrae l'ID del cliente dalla richiesta
     */
    private function extractClientId(Request $request): ?int
    {
        // Cerca in vari posti della richiesta
        
        // 1. Parametro di route {user} o {client}
        if ($request->route('user')) {
            $user = $request->route('user');
            return is_object($user) ? $user->id : $user;
        }
        
        if ($request->route('client')) {
            $client = $request->route('client');
            return is_object($client) ? $client->id : $client;
        }

        // 2. Parametri di query
        if ($request->has('client_id')) {
            return $request->get('client_id');
        }
        
        if ($request->has('user_id')) {
            return $request->get('user_id');
        }

        // 3. Dati del form
        if ($request->has('client_id')) {
            return $request->input('client_id');
        }

        // 4. Per le transazioni, cerca account_id e risali al cliente
        if ($request->has('account_id')) {
            $account = \App\Models\Account::find($request->get('account_id'));
            return $account ? $account->user_id : null;
        }

        return null;
    }
}