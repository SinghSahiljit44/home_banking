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
     * Effettua un bonifico tra due conti
     */
    public function processBonifico(
        Account $fromAccount, 
        string $toIban, 
        float $amount, 
        string $description,
        string $beneficiary = null
    ): array {
        try {
            DB::beginTransaction();

            // Verifica saldo sufficiente
            if (!$fromAccount->hasSufficientBalance($amount)) {
                return [
                    'success' => false,
                    'message' => 'Saldo insufficiente per completare l\'operazione.'
                ];
            }

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
            Log::error('Errore durante il bonifico: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Errore tecnico durante l\'elaborazione del bonifico.'
            ];
        }
    }

    /**
     * Bonifico tra conti interni
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

        $referenceCode = 'TXN' . strtoupper(uniqid());

        // Crea transazione in uscita
        $outTransaction = Transaction::create([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => $amount,
            'type' => 'transfer_out',
            'description' => $description,
            'reference_code' => $referenceCode,
            'status' => 'completed'
        ]);

        // Crea transazione in entrata
        $inTransaction = Transaction::create([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => $amount,
            'type' => 'transfer_in',
            'description' => $description,
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
            'transaction' => $outTransaction
        ];
    }

    /**
     * Bonifico esterno (simulato)
     */
    private function processExternalBonifico(Account $fromAccount, string $toIban, float $amount, string $description, string $beneficiary = null): array
    {
        $referenceCode = 'EXT' . strtoupper(uniqid());

        // Crea transazione in uscita
        $transaction = Transaction::create([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => null, // Conto esterno
            'amount' => $amount,
            'type' => 'transfer_out',
            'description' => $description . ($beneficiary ? " - Beneficiario: {$beneficiary}" : '') . " - IBAN: {$toIban}",
            'reference_code' => $referenceCode,
            'status' => 'pending' // I bonifici esterni richiedono elaborazione
        ]);

        // Blocca temporaneamente i fondi
        $fromAccount->decrement('balance', $amount);

        // Simula l'elaborazione (in produzione sarebbe asincrona)
        // Dopo qualche secondo, la transazione diventerebbe 'completed'
        
        DB::commit();

        return [
            'success' => true,
            'message' => 'Bonifico inviato. L\'operazione sarà elaborata entro 1-2 giorni lavorativi.',
            'reference_code' => $referenceCode,
            'transaction' => $transaction
        ];
    }

    /**
     * Crea un deposito sul conto
     */
    public function createDeposit(Account $account, float $amount, string $description = 'Deposito'): Transaction
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'from_account_id' => null,
                'to_account_id' => $account->id,
                'amount' => $amount,
                'type' => 'deposit',
                'description' => $description,
                'reference_code' => 'DEP' . strtoupper(uniqid()),
                'status' => 'completed'
            ]);

            $account->increment('balance', $amount);

            DB::commit();

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
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
            $transaction = Transaction::create([
                'from_account_id' => $account->id,
                'to_account_id' => null,
                'amount' => $amount,
                'type' => 'withdrawal',
                'description' => $description,
                'reference_code' => 'WTH' . strtoupper(uniqid()),
                'status' => 'completed'
            ]);

            $account->decrement('balance', $amount);

            DB::commit();

            return [
                'success' => true,
                'transaction' => $transaction
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Errore durante il prelievo.'
            ];
        }
    }
}