<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordRecoveryController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Mostra il form per il recupero credenziali
     * AGGIORNATO: Employee vede solo clienti assegnati
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        
        // Lista utenti disponibili in base al ruolo - AGGIORNATO
        if ($currentUser->isAdmin()) {
            // Admin vede tutti gli utenti tranne se stesso
            $query = User::where('id', '!=', $currentUser->id)
                        ->where('is_active', true);
        } else {
            // Employee vede solo i suoi clienti assegnati - CORREZIONE PRINCIPALE
            $query = $currentUser->assignedClients()->where('is_active', true);
        }

        // Applica filtri
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $currentUser->isAdmin()) {
            $query->where('role', $request->get('role'));
        }

        if ($currentUser->isAdmin()) {
            $users = $query->orderBy('role')->orderBy('last_name')->get();
        } else {
            // Per employee, ottieni la collection dai clienti assegnati
            $users = $query->orderBy('last_name')->get();
        }

        // Statistiche
        $stats = [
            'total_available' => $users->count(),
            'clients_count' => $users->where('role', 'client')->count(),
            'employees_count' => $users->where('role', 'employee')->count(),
        ];

        return view('admin.password-recovery.index', compact('users', 'stats'));
    }

    /**
     * Genera nuova password per un utente
     * AGGIORNATO: Controlli specifici per employee
     */
    public function generatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'password_length' => 'integer|min:8|max:32',
            'notify_user' => 'boolean',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        // Verifica permessi - AGGIORNATO
        if (!$this->canResetUserPassword($currentUser, $targetUser)) {
            abort(403, 'Non hai i permessi per resettare la password di questo utente.');
        }

        try {
            // Genera nuova password
            $passwordLength = $request->input('password_length', 12);
            $newPassword = $this->generateSecurePassword($passwordLength);

            // Aggiorna la password
            $targetUser->update([
                'password' => Hash::make($newPassword)
            ]);

            // Log dell'operazione
            \Log::info('Password reset by admin/employee:', [
                'reset_by_id' => $currentUser->id,
                'reset_by_name' => $currentUser->full_name,
                'reset_by_role' => $currentUser->role,
                'target_user_id' => $targetUser->id,
                'target_user_name' => $targetUser->full_name,
                'target_user_role' => $targetUser->role,
                'reason' => $request->reason,
                'notify_user' => $request->boolean('notify_user'),
            ]);

            // Invia notifica se richiesto
            if ($request->boolean('notify_user')) {
                $this->notifyUserOfPasswordReset($targetUser, $newPassword, $currentUser, $request->reason);
            }

            return back()->with('success', "Password resettata con successo per {$targetUser->full_name}")
                        ->with('new_password', $newPassword)
                        ->with('target_user', $targetUser->full_name);

        } catch (\Exception $e) {
            \Log::error('Password reset failed:', [
                'error' => $e->getMessage(),
                'reset_by' => $currentUser->id,
                'target_user' => $targetUser->id,
            ]);

            return back()->withErrors(['general' => 'Errore durante il reset della password.']);
        }
    }

    /**
     * Reset username (solo admin)
     */
    public function resetUsername(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo gli amministratori possono cambiare username.');
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'new_username' => 'required|string|max:50|unique:users,username',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);
        $oldUsername = $targetUser->username;

        try {
            $targetUser->update([
                'username' => $request->new_username
            ]);

            // Log dell'operazione
            \Log::info('Username changed by admin:', [
                'changed_by_id' => $currentUser->id,
                'changed_by_name' => $currentUser->full_name,
                'target_user_id' => $targetUser->id,
                'target_user_name' => $targetUser->full_name,
                'old_username' => $oldUsername,
                'new_username' => $request->new_username,
                'reason' => $request->reason,
            ]);

            return back()->with('success', "Username cambiato da '{$oldUsername}' a '{$request->new_username}' per {$targetUser->full_name}");

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante il cambio username.']);
        }
    }

    /**
     * Sblocca account utente
     */
    public function unlockAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);

        if (!$this->canManageUser($currentUser, $targetUser)) {
            abort(403, 'Non hai i permessi per gestire questo utente.');
        }

        try {
            $targetUser->update(['is_active' => true]);

            // Log dell'operazione
            \Log::info('Account unlocked:', [
                'unlocked_by_id' => $currentUser->id,
                'unlocked_by_name' => $currentUser->full_name,
                'target_user_id' => $targetUser->id,
                'target_user_name' => $targetUser->full_name,
                'reason' => $request->reason,
            ]);

            return back()->with('success', "Account di {$targetUser->full_name} sbloccato con successo.");

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante lo sblocco dell\'account.']);
        }
    }

    /**
     * Verifica se l'utente corrente può resettare la password dell'utente target
     * AGGIORNATO: Controlli specifici per employee
     */
    private function canResetUserPassword(User $currentUser, User $targetUser): bool
    {
        // Admin può resettare password di tutti tranne se stesso
        if ($currentUser->isAdmin()) {
            return $currentUser->id !== $targetUser->id;
        }

        // Employee può resettare solo password dei clienti assegnati - CORREZIONE
        if ($currentUser->isEmployee()) {
            return $targetUser->isClient() && $currentUser->canManageClient($targetUser);
        }

        return false;
    }

    /**
     * Verifica se l'utente corrente può gestire l'utente target
     * AGGIORNATO: Controlli specifici per employee
     */
    private function canManageUser(User $currentUser, User $targetUser): bool
    {
        // Admin può gestire tutti tranne se stesso
        if ($currentUser->isAdmin()) {
            return $currentUser->id !== $targetUser->id;
        }

        // Employee può gestire solo i clienti assegnati - CORREZIONE
        if ($currentUser->isEmployee()) {
            return $targetUser->isClient() && $currentUser->canManageClient($targetUser);
        }

        return false;
    }

    /**
     * API endpoint per cercare utenti (per autocompletamento)
     * AGGIORNATO: Filtri specifici per employee
     */
    public function searchUsers(Request $request)
    {
        $currentUser = Auth::user();
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        if ($currentUser->isAdmin()) {
            $users = User::where('id', '!=', $currentUser->id)
                        ->where('is_active', true)
                        ->where(function($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%")
                                  ->orWhere('username', 'like', "%{$search}%");
                        })
                        ->select('id', 'first_name', 'last_name', 'email', 'username', 'role')
                        ->limit(10)
                        ->get();
        } else {
            // Employee vede solo i suoi clienti assegnati - CORREZIONE
            $users = $currentUser->assignedClients()
                                 ->where(function($query) use ($search) {
                                     $query->where('first_name', 'like', "%{$search}%")
                                           ->orWhere('last_name', 'like', "%{$search}%")
                                           ->orWhere('email', 'like', "%{$search}%")
                                           ->orWhere('username', 'like', "%{$search}%");
                                 })
                                 ->select('id', 'first_name', 'last_name', 'email', 'username', 'role')
                                 ->limit(10)
                                 ->get();
        }

        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => ucfirst($user->role),
            ];
        }));
    }

    /**
     * Genera una password sicura
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*';

        // Assicurati che ci sia almeno un carattere per tipo
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Riempi il resto
        $allChars = $lowercase . $uppercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Mescola i caratteri
        return str_shuffle($password);
    }

    /**
     * Invia notifica all'utente del reset password
     */
    private function notifyUserOfPasswordReset(User $user, string $newPassword, User $resetBy, string $reason): void
    {
        try {
            // In un'implementazione reale, useresti il sistema di email di Laravel
            // Per ora logghiamo solo l'operazione
            \Log::info('Password reset notification should be sent:', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'reset_by' => $resetBy->full_name,
                'reason' => $reason,
                // NON loggare la password in produzione
            ]);

            // TODO: Implementare invio email
            // Mail::to($user->email)->send(new PasswordResetNotification($user, $newPassword, $resetBy, $reason));

        } catch (\Exception $e) {
            \Log::error('Failed to send password reset notification:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostra il log delle operazioni di recupero password
     */
    public function auditLog(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo gli amministratori possono vedere i log di audit.');
        }

        // In un'implementazione reale, avresti una tabella dedicata ai log
        // Per ora mostriamo una vista placeholder
        return view('admin.password-recovery.audit-log');
    }

    /**
     * Genera credenziali multiple (bulk) - Solo Admin
     */
    public function bulkReset(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo gli amministratori possono effettuare reset multipli.');
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'password_length' => 'integer|min:8|max:32',
            'reason' => 'required|string|max:500',
            'notify_users' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $currentUser = Auth::user();
        $passwordLength = $request->input('password_length', 12);
        $results = [];
        $errors = [];

        foreach ($request->user_ids as $userId) {
            try {
                $targetUser = User::findOrFail($userId);

                if (!$this->canResetUserPassword($currentUser, $targetUser)) {
                    $errors[] = "Non hai i permessi per {$targetUser->full_name}";
                    continue;
                }

                $newPassword = $this->generateSecurePassword($passwordLength);
                $targetUser->update(['password' => Hash::make($newPassword)]);

                $results[] = [
                    'user' => $targetUser,
                    'password' => $newPassword,
                ];

                // Log dell'operazione
                \Log::info('Bulk password reset:', [
                    'reset_by_id' => $currentUser->id,
                    'target_user_id' => $targetUser->id,
                    'reason' => $request->reason,
                ]);

                // Notifica se richiesto
                if ($request->boolean('notify_users')) {
                    $this->notifyUserOfPasswordReset($targetUser, $newPassword, $currentUser, $request->reason);
                }

            } catch (\Exception $e) {
                $errors[] = "Errore per utente ID {$userId}: " . $e->getMessage();
            }
        }

        if (count($results) > 0) {
            $message = count($results) . " password resettate con successo";
            if (count($errors) > 0) {
                $message .= ", " . count($errors) . " errori";
            }

            return back()->with('success', $message)
                        ->with('bulk_results', $results)
                        ->with('bulk_errors', $errors);
        } else {
            return back()->withErrors(['general' => 'Nessuna password è stata resettata.'])
                        ->with('bulk_errors', $errors);
        }
    }
}