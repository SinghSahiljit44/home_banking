<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'iban',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    /**
     * Ottiene tutte le transazioni relative a questo conto (sia in entrata che in uscita)
     * Gestisce correttamente i bonifici interni per evitare duplicati
     */
    public function allTransactions()
    {
        return Transaction::where(function($query) {
            $query->where('from_account_id', $this->id)
                  ->orWhere('to_account_id', $this->id);
        })
        ->orderBy('created_at', 'desc');
    }

    /**
     * Verifica se una transazione è in entrata per questo conto
     */
    public function isIncomingTransaction(Transaction $transaction): bool
    {
        return $transaction->to_account_id === $this->id && $transaction->from_account_id !== $this->id;
    }

    /**
     * Verifica se una transazione è in uscita per questo conto
     */
    public function isOutgoingTransaction(Transaction $transaction): bool
    {
        return $transaction->from_account_id === $this->id;
    }

    /**
     * Ottiene il tipo di transazione dal punto di vista di questo conto
     */
    public function getTransactionType(Transaction $transaction): string
    {
        if ($this->isIncomingTransaction($transaction)) {
            return 'incoming';
        } elseif ($this->isOutgoingTransaction($transaction)) {
            return 'outgoing';
        }
        
        return 'unknown';
    }

    /**
     * Ottiene l'importo della transazione dal punto di vista di questo conto
     */
    public function getTransactionAmount(Transaction $transaction): float
    {
        if ($this->isIncomingTransaction($transaction)) {
            return $transaction->amount; // Positivo
        } elseif ($this->isOutgoingTransaction($transaction)) {
            return -$transaction->amount; // Negativo
        }
        
        return 0;
    }

    /**
     * Ottiene la descrizione della transazione dal punto di vista di questo conto
     */
    public function getTransactionDescription(Transaction $transaction): string
    {
        $description = $transaction->description;
        
        // Se è un bonifico interno e questo conto è il destinatario
        if ($this->isIncomingTransaction($transaction) && $transaction->fromAccount) {
            $description = "Bonifico ricevuto da " . $transaction->fromAccount->user->full_name . " - " . $description;
        }
        
        return $description;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }
}