<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    use HasRoles; // Aggiunto per Spatie Permission

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'address',
        'role', // Manteniamo per compatibilità
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

    // Relazioni esistenti dal tuo progetto
    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function otpTokens(): HasMany
    {
        return $this->hasMany(OtpToken::class);
    }

    public function securityQuestion(): HasOne
    {
        return $this->hasOne(SecurityQuestion::class);
    }

    // Metodi helper per i ruoli (semplificati per progetto universitario)
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

        public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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
        // Return a default avatar or empty string if no profile photo system
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
        return collect(); // Empty collection
    }

    /**
     * For Jetstream team compatibility (returns null if not using teams)
     */
    public function getCurrentTeamAttribute()
    {
        return null;
    }

}