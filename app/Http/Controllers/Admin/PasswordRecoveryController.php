<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeClientAssignment;
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
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        
        // Lista utenti disponibili in base al ruolo
        if ($currentUser->isAdmin()) {
            $query = User::where('id', '!=', $currentUser->id)
                        ->where('role', '!=', 'admin') 
                        ->where('is_active', true);
        } else {
            // Employee vede solo i suoi clienti assegnati
            $clientIds = EmployeeClientAssignment::where('employee_id', $currentUser->id)
                                                ->where('is_active', true)
                                                ->pluck('client_id');
            
            $query = User::whereIn('id', $clientIds)
                        ->where('role', 'client')
                        ->where('is_active', true);
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
            $role = $request->get('role');
            if ($role !== 'admin') { 
                $query->where('role', $role);
            }
        }

        $users = $query->orderBy('role')->orderBy('last_name')->get();

        // Statistiche 
        if ($currentUser->isAdmin()) {
            $stats = [
                'total_available' => $users->count(),
                'clients_count' => $users->where('role', 'client')->count(),
                'employees_count' => 0,
            ];
        } else {
            $stats = [
                'total_available' => $users->count(),
                'clients_count' => $users->where('role', 'client')->count(),
                'employees_count' => 0, // Employee non gestisce altri employee
            ];
        }

        return view('admin.password-recovery.index', compact('users', 'stats'));
    }

    /**
     * Genera nuova password per un utente
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
     * Reset username 
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
     * 
     */
    private function canResetUserPassword(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->isAdmin()) {
            if ($currentUser->id === $targetUser->id) {
                return false; 
            }
            
            if ($targetUser->isAdmin()) {
                return false; 
            }
            
            return true; 
        }

        if ($currentUser->isEmployee()) {
            return $targetUser->isClient() && $currentUser->canManageClient($targetUser);
        }

        return false;
    }

    /**
     * Verifica se l'utente corrente può gestire l'utente target
     */
    private function canManageUser(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->isAdmin()) {
            if ($currentUser->id === $targetUser->id) {
                return false; 
            }
            
            if ($targetUser->isAdmin()) {
                return false; 
            }
            
            return true; 
        }

        if ($currentUser->isEmployee()) {
            return $targetUser->isClient() && $currentUser->canManageClient($targetUser);
        }

        return false;
    }

    /**
     * Filtri specifici per employee 
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
            $clientIds = EmployeeClientAssignment::where('employee_id', $currentUser->id)
                                                ->where('is_active', true)
                                                ->pluck('client_id');
            
            $users = User::whereIn('id', $clientIds)
                        ->where('role', 'client')
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
     * Mostra il log delle operazioni di recupero password
     */
    public function auditLog(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo gli amministratori possono vedere i log di audit.');
        }
        return view('admin.password-recovery.audit-log');
    }

    /**
     * Genera credenziali multiple 
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

                \Log::info('Bulk password reset:', [
                    'reset_by_id' => $currentUser->id,
                    'target_user_id' => $targetUser->id,
                    'reason' => $request->reason,
                ]);

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