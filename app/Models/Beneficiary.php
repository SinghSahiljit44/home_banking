<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'iban',
        'bank_name',
        'notes',
        'is_favorite'
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatta l'IBAN con spazi per la visualizzazione
     */
    public function getFormattedIbanAttribute(): string
    {
        return chunk_split($this->iban, 4, ' ');
    }
}