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

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // NUOVE RELAZIONI PER EMPLOYEE-CLIENT ASSIGNMENTS

    /**
     * Clienti assegnati a questo employee
     */
    public function assignedClients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_client_assignments', 'employee_id', 'client_id')
                    ->withPivot(['assigned_by', 'is_active', 'notes', 'assigned_at'])
                    ->withTimestamps()
                    ->wherePivot('is_active', true);
    }

    /**
     * Employee a cui è assegnato questo cliente
     */
    public function assignedEmployees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_client_assignments', 'client_id', 'employee_id')
                    ->withPivot(['assigned_by', 'is_active', 'notes', 'assigned_at'])
                    ->withTimestamps()
                    ->wherePivot('is_active', true);
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

    // Metodi helper per i ruoli (esistenti)
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

    // NUOVI METODI HELPER PER PERMISSIONS

    /**
     * Verifica se questo employee può gestire il cliente specificato
     */
    public function canManageClient(User $client): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin può gestire tutti
        }

        if (!$this->isEmployee()) {
            return false; // Solo employee e admin possono gestire clienti
        }

        return $this->assignedClients()->where('users.id', $client->id)->exists();
    }

    /**
     * Verifica se questo employee può vedere le transazioni del cliente
     */
    public function canViewClientTransactions(User $client): bool
    {
        return $this->canManageClient($client);
    }

    /**
     * Verifica se questo employee può fare bonifici per il cliente
     */
    public function canMakeTransfersForClient(User $client): bool
    {
        return $this->canManageClient($client);
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
            return $this->assignedClients;
        }

        return collect(); // I clienti non possono gestire altri clienti
    }

    /**
     * Ottieni tutte le transazioni visibili a questo utente
     */
    public function getVisibleTransactions()
    {
        if ($this->isAdmin()) {
            return Transaction::all(); // Admin vede tutto
        }

        if ($this->isEmployee()) {
            // Employee vede solo transazioni dei clienti assegnati
            $clientIds = $this->assignedClients()->pluck('users.id');
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

        return collect();
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