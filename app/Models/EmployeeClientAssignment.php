<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeClientAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'client_id',
        'assigned_by',
        'is_active',
        'notes',
        'assigned_at'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    /**
     * Employee assegnato
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Cliente assegnato
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Admin che ha fatto l'assegnazione
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope per assegnazioni attive
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per un employee specifico
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope per un cliente specifico
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}