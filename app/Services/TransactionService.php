<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Effettua un bonifico tra due conti - VERSIONE CORRETTA SENZA DUPLICATI
     */
    public function processBonifico(
        Account $fromAccount, 
        string $toIban, 
        float $amount, 
        string $description,
        string $beneficiary = null
    ): array {
        try {
            \Log::info('processBonifico started', [
                'from_account_id' => $fromAccount->id,
                'to_iban' => $toIban,
                'amount' => $amount,
                'description' => $description
            ]);

            DB::beginTransaction();

            // Verifica saldo sufficiente
            if (!$fromAccount->hasSufficientBalance($amount)) {
                \Log::error('Insufficient balance', [
                    'required' => $amount,
                    'available' => $fromAccount->balance
                ]);
                return [
                    'success' => false,
                    'message' => 'Saldo insufficiente per completare l\'operazione.'
                ];
            }

            \Log::info('Balance check passed');

            // Trova il conto di destinazione
            $toAccount = Account::where('iban', $toIban)->first();
            
            if (!$toAccount) {
                // Bonifico esterno (simulato)
                return $this->processExternalBonifico($fromAccount, $toIban, $amount, $description, $beneficiary);
            }

            // Bonifico interno
            return $this->processInternalBonifico($fromAccount, $toAccount, $amount, $description);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Errore durante il bonifico:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Errore tecnico: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bonifico interno tra conti della stessa banca - CORRETTO
     */
    private function processInternalBonifico(Account $fromAccount, Account $toAccount, float $amount, string $description): array
    {
        // Verifica che il conto di destinazione sia attivo
        if (!$toAccount->is_active) {
            return [
                'success' => false,
                'message' => 'Il conto di destinazione non è attivo.'
            ];
        }

        // Genera codice di riferimento univoco
        $referenceCode = $this->generateUniqueReferenceCode();

        // Pulisci la descrizione da caratteri problematici
        $cleanDescription = $this->cleanDescription($description);

        // CAMBIAMENTO PRINCIPALE: Crea una SOLA transazione per il bonifico interno
        // La transazione viene vista dal punto di vista del mittente
        $transaction = Transaction::create([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => $amount,
            'type' => 'transfer_out', // Dal punto di vista del mittente
            'description' => $cleanDescription,
            'reference_code' => $referenceCode,
            'status' => 'completed'
        ]);

        // Aggiorna i saldi
        $fromAccount->decrement('balance', $amount);
        $toAccount->increment('balance', $amount);

        DB::commit();

        return [
            'success' => true,
            'message' => 'Bonifico completato con successo.',
            'reference_code' => $referenceCode,
            'transaction' => $transaction
        ];
    }

    /**
     * Bonifico esterno (simulato)
     */
    private function processExternalBonifico(Account $fromAccount, string $toIban, float $amount, string $description, string $beneficiary = null): array
    {
        // Genera codice di riferimento univoco
        $referenceCode = $this->generateUniqueReferenceCode('EXT');

        // Pulisci la descrizione
        $cleanDescription = $this->cleanDescription($description);
        if ($beneficiary) {
            $cleanDescription .= ' - Beneficiario: ' . $this->cleanDescription($beneficiary);
        }
        $cleanDescription .= ' - IBAN: ' . $toIban;

        // Crea transazione in uscita
        $transaction = Transaction::create([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => null, // Conto esterno
            'amount' => $amount,
            'type' => 'transfer_out',
            'description' => $cleanDescription,
            'reference_code' => $referenceCode,
            'status' => 'completed'
        ]);

        // Aggiorna saldo
        $fromAccount->decrement('balance', $amount);
        
        DB::commit();

        return [
            'success' => true,
            'message' => 'Bonifico inviato con successo.',
            'reference_code' => $referenceCode,
            'transaction' => $transaction
        ];
    }

    /**
     * Genera un codice di riferimento univoco
     */
    private function generateUniqueReferenceCode(string $prefix = 'TXN'): string
    {
        do {
            $referenceCode = $prefix . strtoupper(uniqid()) . rand(100, 999);
        } while (Transaction::where('reference_code', $referenceCode)->exists());
        
        return $referenceCode;
    }

    /**
     * Pulisce la descrizione da caratteri problematici
     */
    private function cleanDescription(string $description): string
    {
        // Converte caratteri speciali in equivalenti ASCII
        $description = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $description);
        // Rimuove caratteri non stampabili
        $description = preg_replace('/[^\x20-\x7E]/', '', $description);
        // Limita la lunghezza
        return substr(trim($description), 0, 250);
    }

    /**
     * Crea un deposito sul conto - FIXED: NON MODIFICA IL SALDO DIRETTAMENTE
     */
    public function createDeposit(Account $account, float $amount, string $description = 'Deposito'): Transaction
    {
        DB::beginTransaction();

        try {
            // Genera codice di riferimento
            $referenceCode = $this->generateUniqueReferenceCode('DEP');
            
            // IMPORTANTE: Crea SOLO la transazione, il saldo verrà aggiornato separatamente
            $transaction = Transaction::create([
                'from_account_id' => null,
                'to_account_id' => $account->id,
                'amount' => $amount,
                'type' => 'deposit',
                'description' => $this->cleanDescription($description),
                'reference_code' => $referenceCode,
                'status' => 'completed'
            ]);

            // AGGIORNA IL SALDO SOLO UNA VOLTA
            $account->increment('balance', $amount);

            DB::commit();

            \Log::info('Deposit created successfully', [
                'account_id' => $account->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'reference_code' => $referenceCode,
                'new_balance' => $account->fresh()->balance
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Deposit creation failed', [
                'account_id' => $account->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Crea un prelievo dal conto
     */
    public function createWithdrawal(Account $account, float $amount, string $description = 'Prelievo'): array
    {
        if (!$account->hasSufficientBalance($amount)) {
            return [
                'success' => false,
                'message' => 'Saldo insufficiente.'
            ];
        }

        DB::beginTransaction();

        try {
            $referenceCode = $this->generateUniqueReferenceCode('WTH');
            
            $transaction = Transaction::create([
                'from_account_id' => $account->id,
                'to_account_id' => null,
                'amount' => $amount,
                'type' => 'withdrawal',
                'description' => $this->cleanDescription($description),
                'reference_code' => $referenceCode,
                'status' => 'completed'
            ]);

            $account->decrement('balance', $amount);

            DB::commit();

            \Log::info('Withdrawal created successfully', [
                'account_id' => $account->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'reference_code' => $referenceCode,
                'new_balance' => $account->fresh()->balance
            ]);

            return [
                'success' => true,
                'transaction' => $transaction
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Withdrawal creation failed', [
                'account_id' => $account->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Errore durante il prelievo.'
            ];
        }
    }
}