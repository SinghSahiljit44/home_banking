<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user - ELIMINAZIONE FISICA COMPLETA
     */
    public function delete(User $user): void
    {
        try {
            \DB::beginTransaction();

            // Log dell'eliminazione prima di procedere
            \Log::warning('User self-deletion initiated:', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_email' => $user->email,
                'account_balance' => $user->account ? $user->account->balance : 0,
                'deletion_type' => 'SELF_DELETE',
                'timestamp' => now()->toISOString(),
            ]);

            // 1. Elimina foto profilo se presente
            $user->deleteProfilePhoto();
            
            // 2. Elimina tutti i token API
            $user->tokens->each->delete();

            // 3. Elimina tutte le transazioni associate all'account
            if ($user->account) {
                // Elimina transazioni in entrata
                $user->account->incomingTransactions()->delete();
                
                // Elimina transazioni in uscita  
                $user->account->outgoingTransactions()->delete();
                
                // Elimina l'account
                $user->account->delete();
            }

            // 4. Elimina le assegnazioni employee-client
            if ($user->isEmployee()) {
                $user->employeeAssignments()->delete();
            }
            
            if ($user->isClient()) {
                $user->clientAssignments()->delete();
            }

            // 6. Elimina le domande di sicurezza
            if ($user->securityQuestion) {
                $user->securityQuestion->delete();
            }

            // 7. Elimina definitivamente l'utente
            $user->delete();

            \DB::commit();

            \Log::info('User successfully deleted:', [
                'deletion_completed' => true,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('User deletion failed:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}