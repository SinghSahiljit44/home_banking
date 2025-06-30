<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'address',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relazioni esistenti
    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function securityQuestion(): HasOne
    {
        return $this->hasOne(SecurityQuestion::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    // RELAZIONI PER EMPLOYEE-CLIENT ASSIGNMENTS - CORRETTE

    /**
     * Clienti assegnati a questo employee (relazione many-to-many)
     */
    public function assignedClients(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 
            'employee_client_assignments', 
            'employee_id', 
            'client_id'
        )
        ->withPivot(['assigned_by', 'is_active', 'notes', 'assigned_at'])
        ->withTimestamps()
        ->wherePivot('is_active', true)
        ->where('role', 'client'); // Assicura che siano solo clienti
    }

    /**
     * Employee a cui è assegnato questo cliente
     */
    public function assignedEmployees(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 
            'employee_client_assignments', 
            'client_id', 
            'employee_id'
        )
        ->withPivot(['assigned_by', 'is_active', 'notes', 'assigned_at'])
        ->withTimestamps()
        ->wherePivot('is_active', true)
        ->where('role', 'employee'); // Assicura che siano solo employee
    }

    /**
     * Tutte le assegnazioni come employee
     */
    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeClientAssignment::class, 'employee_id');
    }

    /**
     * Tutte le assegnazioni come cliente
     */
    public function clientAssignments(): HasMany
    {
        return $this->hasMany(EmployeeClientAssignment::class, 'client_id');
    }

    /**
     * Assegnazioni create da questo admin
     */
    public function createdAssignments(): HasMany
    {
        return $this->hasMany(EmployeeClientAssignment::class, 'assigned_by');
    }

    // Metodi helper per i ruoli
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    // METODI HELPER PER PERMISSIONS - RIVISTI E CORRETTI

    /**
     * Verifica se questo employee può gestire il cliente specificato
     */
    public function canManageClient(User $client): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin può gestire tutti
        }

        if (!$this->isEmployee() || !$client->isClient()) {
            return false; // Solo employee possono gestire clienti
        }

        // Verifica attraverso la tabella pivot direttamente
        return EmployeeClientAssignment::where('employee_id', $this->id)
                                      ->where('client_id', $client->id)
                                      ->where('is_active', true)
                                      ->exists();
    }

    /**
     * Verifica se questo employee può vedere le transazioni del cliente
     */
    public function canViewClientTransactions(User $client): bool
    {
        return $this->canManageClient($client);
    }

    /**
     * Verifica bonifici - Employee SOLO per assegnati
     */
    public function canMakeTransfersForClient(User $client): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin può fare bonifici per tutti
        }

        if ($this->isEmployee()) {
            return $this->canManageClient($client); // SOLO clienti assegnati
        }

        return false;
    }

    /**
     * Verifica depositi - Employee può fare per TUTTI
     */
    public function canMakeDepositsForClient(User $client): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin può fare depositi per tutti
        }

        if ($this->isEmployee()) {
            return $client->isClient(); // Employee può fare depositi per TUTTI i clienti
        }

        return false;
    }

    /**
     * Recupero credenziali - Employee SOLO per assegnati
     */
    public function canRecoverCredentialsForClient(User $client): bool
    {
        if ($this->isAdmin()) {
            return $client->id !== $this->id; // Admin per tutti tranne se stesso
        }

        if ($this->isEmployee()) {
            return $this->canManageClient($client); // SOLO clienti assegnati
        }

        return false;
    }

    /**
     * Verifica se può rimuovere un utente
     */
    public function canRemoveUser(User $targetUser): bool
    {
        if ($this->isAdmin()) {
            // Admin può rimuovere tutti tranne altri admin e se stesso
            return !$targetUser->isAdmin() && $this->id !== $targetUser->id;
        }

        if ($this->isEmployee()) {
            // Employee può rimuovere solo clienti assegnati
            return $targetUser->isClient() && $this->canManageClient($targetUser);
        }

        return false; // I clienti non possono rimuovere nessuno
    }

    /**
     * Verifica se può gestire le credenziali di un utente
     */
    public function canManageUserCredentials(User $targetUser): bool
    {
        if ($this->isAdmin()) {
            // Admin può gestire tutti tranne altri admin (eccetto se stesso per alcune operazioni)
            if ($targetUser->isAdmin()) {
                return $this->id === $targetUser->id; // Solo le proprie credenziali di admin
            }
            return true;
        }

        if ($this->isEmployee()) {
            // Employee può gestire solo clienti assegnati
            return $targetUser->isClient() && $this->canManageClient($targetUser);
        }

        // I clienti possono gestire solo le proprie credenziali
        return $this->id === $targetUser->id;
    }

    /**
     * Verifica se può vedere le transazioni di un utente
     */
    public function canViewUserTransactions(User $targetUser): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin vede tutte le transazioni
        }

        if ($this->isEmployee()) {
            return $targetUser->isClient() && $this->canManageClient($targetUser);
        }

        // Cliente vede solo le proprie transazioni
        return $this->id === $targetUser->id;
    }

    /**
     * Verifica se può bloccare/sbloccare un utente
     */
    public function canToggleUserStatus(User $targetUser): bool
    {
        if ($this->isAdmin()) {
            // Admin può gestire tutti tranne altri admin e se stesso
            return !$targetUser->isAdmin() && $this->id !== $targetUser->id;
        }

        if ($this->isEmployee()) {
            // Employee può gestire solo clienti assegnati
            return $targetUser->isClient() && $this->canManageClient($targetUser);
        }

        return false;
    }

    /**
     * Ottieni tutti i clienti gestibili da questo utente
     */
    public function getManageableClients()
    {
        if ($this->isAdmin()) {
            return User::where('role', 'client')->get();
        }

        if ($this->isEmployee()) {
            // Usa query diretta per evitare problemi con la relazione
            $clientIds = EmployeeClientAssignment::where('employee_id', $this->id)
                                                ->where('is_active', true)
                                                ->pluck('client_id');
            
            return User::whereIn('id', $clientIds)->where('role', 'client')->get();
        }

        return collect(); // I clienti non possono gestire altri clienti
    }

    /**
     * Ottieni tutte le transazioni visibili a questo utente
     */
    public function getVisibleTransactions()
    {
        if ($this->isAdmin()) {
            return Transaction::query(); // Admin vede tutto
        }

        if ($this->isEmployee()) {
            // Employee vede solo transazioni dei clienti assegnati
            $clientIds = EmployeeClientAssignment::where('employee_id', $this->id)
                                                ->where('is_active', true)
                                                ->pluck('client_id');
            
            $accountIds = Account::whereIn('user_id', $clientIds)->pluck('id');
            
            return Transaction::where(function($query) use ($accountIds) {
                $query->whereIn('from_account_id', $accountIds)
                      ->orWhereIn('to_account_id', $accountIds);
            });
        }

        if ($this->isClient() && $this->account) {
            // Cliente vede solo le sue transazioni
            return $this->account->allTransactions();
        }

        return Transaction::whereRaw('1 = 0'); // Query vuota
    }

    /**
     * Ottieni tutti i clienti per cui può fare depositi
     */
    public function getClientsForDeposits()
    {
        if ($this->isAdmin()) {
            return User::where('role', 'client')->where('is_active', true)->get();
        }

        if ($this->isEmployee()) {
            // Employee può fare depositi per TUTTI i clienti
            return User::where('role', 'client')->where('is_active', true)->get();
        }

        return collect();
    }

    /**
     * Ottieni clienti per cui può fare bonifici
     */
    public function getClientsForTransfers()
    {
        if ($this->isAdmin()) {
            return User::where('role', 'client')->where('is_active', true)->get();
        }

        if ($this->isEmployee()) {
            // Employee può fare bonifici SOLO per clienti assegnati
            return $this->getManageableClients();
        }

        return collect();
    }

    /**
     * Ottieni clienti per cui può recuperare credenziali
     */
    public function getClientsForCredentialRecovery()
    {
        if ($this->isAdmin()) {
            return User::where('role', '!=', 'admin')
                      ->where('id', '!=', $this->id)
                      ->where('is_active', true)
                      ->get();
        }

        if ($this->isEmployee()) {
            // Employee può recuperare credenziali SOLO per clienti assegnati
            return $this->getManageableClients();
        }

        return collect();
    }

    /**
     * Verifica se è il manager di questo cliente
     */
    public function isManagerOf(User $client): bool
    {
        if (!$this->isEmployee() || !$client->isClient()) {
            return false;
        }

        return $this->canManageClient($client);
    }

    /**
     * Ottieni statistiche per questo utente - CORRETTO
     */
    public function getStatsForRole(): array
    {
        $stats = [];

        if ($this->isAdmin()) {
            $stats = [
                'total_users' => User::count(),
                'total_clients' => User::where('role', 'client')->count(),
                'total_employees' => User::where('role', 'employee')->count(),
                'total_transactions' => Transaction::count(),
                'total_accounts' => Account::count(),
                'total_balance' => Account::sum('balance'),
            ];
        } elseif ($this->isEmployee()) {
            $clientIds = EmployeeClientAssignment::where('employee_id', $this->id)
                                                ->where('is_active', true)
                                                ->pluck('client_id');
            
            $accountIds = Account::whereIn('user_id', $clientIds)->pluck('id');
            
            $stats = [
                'assigned_clients' => $clientIds->count(),
                'client_transactions' => Transaction::where(function($query) use ($accountIds) {
                    $query->whereIn('from_account_id', $accountIds)
                          ->orWhereIn('to_account_id', $accountIds);
                })->count(),
                'client_total_balance' => Account::whereIn('user_id', $clientIds)->sum('balance'),
            ];
        } elseif ($this->isClient() && $this->account) {
            $stats = [
                'account_balance' => $this->account->balance,
                'total_transactions' => $this->account->allTransactions()->count(),
                'incoming_amount' => $this->account->incomingTransactions()->sum('amount'),
                'outgoing_amount' => $this->account->outgoingTransactions()->sum('amount'),
            ];
        }

        return $stats;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the user's display name (compatibility with Jetstream)
     */
    public function getNameAttribute(): ?string
    {
        return $this->full_name ?? $this->username ?? 'User';
    }

    /**
     * Get profile photo URL (compatibility with Jetstream navigation)
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if user has a specific role (for middleware compatibility)
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * For Jetstream team compatibility (returns empty collection if not using teams)
     */
    public function allTeams()
    {
        return collect();
    }

    /**
     * For Jetstream team compatibility (returns null if not using teams)
     */
    public function getCurrentTeamAttribute()
    {
        return null;
    }
}