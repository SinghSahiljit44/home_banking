<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $transactionService = app(TransactionService::class);

        // Esegui prima AdminSeeder se non già fatto
        $this->call(AdminSeeder::class);

        // Crea alcuni clienti aggiuntivi con conti
        $clients = [
            [
                'username' => 'mario.rossi',
                'email' => 'mario.rossi@email.com',
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'phone' => '+39 333 1234567',
                'address' => 'Via Roma 123, 20100 Milano',
                'initial_balance' => 5000.00
            ],
            [
                'username' => 'giulia.bianchi',
                'email' => 'giulia.bianchi@email.com',
                'first_name' => 'Giulia',
                'last_name' => 'Bianchi',
                'phone' => '+39 333 2345678',
                'address' => 'Corso Italia 456, 10100 Torino',
                'initial_balance' => 3500.50
            ],
            [
                'username' => 'franco.verdi',
                'email' => 'franco.verdi@email.com',
                'first_name' => 'Franco',
                'last_name' => 'Verdi',
                'phone' => '+39 333 3456789',
                'address' => 'Piazza San Marco 789, 30100 Venezia',
                'initial_balance' => 7500.75
            ],
            [
                'username' => 'laura.neri',
                'email' => 'laura.neri@email.com',
                'first_name' => 'Laura',
                'last_name' => 'Neri',
                'phone' => '+39 333 4567890',
                'address' => 'Via del Corso 321, 00100 Roma',
                'initial_balance' => 2000.25
            ]
        ];

        $createdUsers = [];

        foreach ($clients as $clientData) {
            // Crea l'utente se non esiste
            $user = User::firstOrCreate(
                ['username' => $clientData['username']],
                [
                    'email' => $clientData['email'],
                    'password' => Hash::make('password123'),
                    'first_name' => $clientData['first_name'],
                    'last_name' => $clientData['last_name'],
                    'phone' => $clientData['phone'],
                    'address' => $clientData['address'],
                    'role' => 'client',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assegna il ruolo
            if (!$user->hasRole('client')) {
                $user->assignRole('client');
            }

            // Crea il conto se non esiste
            if (!$user->account) {
                $account = $this->createAccountForUser($user, $clientData['initial_balance']);
                
                // Deposito iniziale
                if ($clientData['initial_balance'] > 0) {
                    $transactionService->createDeposit(
                        $account, 
                        $clientData['initial_balance'], 
                        'Deposito iniziale - Apertura conto'
                    );
                }
            }

            $createdUsers[] = $user;
        }

        // Crea alcune transazioni di esempio tra i conti
        $this->createSampleTransactions($createdUsers, $transactionService);

        $this->command->info('Demo data creati con successo!');
        $this->command->info('Utenti creati:');
        foreach ($clients as $client) {
            $this->command->info("- {$client['username']} (password: password123)");
        }
    }

    /**
     * Crea un conto per l'utente
     */
    private function createAccountForUser(User $user, float $initialBalance = 0): Account
    {
        // Genera numero conto univoco
        do {
            $accountNumber = '10' . str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $accountNumber)->exists());

        // Genera IBAN italiano
        $bankCode = '05428'; // Codice banca fittizio
        $branchCode = '11101'; // Codice filiale
        $accountCode = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        
        $bban = $bankCode . $branchCode . $accountCode;
        $checkDigits = $this->calculateIbanCheckDigits('IT', $bban);
        $iban = 'IT' . $checkDigits . $bban;

        return Account::create([
            'user_id' => $user->id,
            'account_number' => $accountNumber,
            'iban' => $iban,
            'balance' => $initialBalance,
            'is_active' => true,
        ]);
    }

    /**
     * Calcola i digit di controllo IBAN
     */
    private function calculateIbanCheckDigits(string $countryCode, string $bban): string
    {
        // Algoritmo semplificato per generare check digits IBAN
        $rearranged = $bban . $countryCode . '00';
        $numericString = '';
        
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numericString .= (ord(strtoupper($char)) - ord('A') + 10);
            } else {
                $numericString .= $char;
            }
        }
        
        $checksum = 98 - bcmod($numericString, '97');
        return str_pad($checksum, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Crea transazioni di esempio
     */
    private function createSampleTransactions(array $users, TransactionService $transactionService): void
    {
        if (count($users) < 2) {
            return;
        }

        $transactions = [
            [
                'from' => $users[0],
                'to' => $users[1],
                'amount' => 150.00,
                'description' => 'Rimborso cena',
                'days_ago' => 5
            ],
            [
                'from' => $users[1],
                'to' => $users[2],
                'amount' => 75.50,
                'description' => 'Pagamento bolletta condominiale',
                'days_ago' => 3
            ],
            [
                'from' => $users[2],
                'to' => $users[0],
                'amount' => 200.00,
                'description' => 'Prestito personale',
                'days_ago' => 7
            ],
            [
                'from' => $users[0],
                'to' => $users[3],
                'amount' => 50.25,
                'description' => 'Regalo compleanno',
                'days_ago' => 1
            ]
        ];

        foreach ($transactions as $txnData) {
            // Verifica che entrambi gli utenti abbiano un conto
            if (!$txnData['from']->account || !$txnData['to']->account) {
                continue;
            }

            // Controlla saldo sufficiente
            if (!$txnData['from']->account->hasSufficientBalance($txnData['amount'])) {
                continue;
            }

            // Crea la transazione
            $result = $transactionService->processBonifico(
                $txnData['from']->account,
                $txnData['to']->account->iban,
                $txnData['amount'],
                $txnData['description']
            );

            // Modifica la data della transazione per simulare cronologia
            if ($result['success'] && isset($result['transaction'])) {
                $transaction = $result['transaction'];
                $createdAt = now()->subDays($txnData['days_ago']);
                
                $transaction->update(['created_at' => $createdAt]);
                
                // Aggiorna anche la transazione di entrata se esiste
                $inTransaction = Transaction::where('reference_code', $transaction->reference_code)
                    ->where('type', 'transfer_in')
                    ->first();
                
                if ($inTransaction) {
                    $inTransaction->update(['created_at' => $createdAt]);
                }
            }
        }
    }
}