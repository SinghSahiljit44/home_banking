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

        // ========== AMMINISTRATORI AGGIUNTIVI ==========
        $admins = [
            [
                'username' => 'admin.sistema',
                'email' => 'admin.sistema@homebanking.com',
                'first_name' => 'Marco',
                'last_name' => 'Amministratore',
                'phone' => '+39 333 0000001',
                'address' => 'Via del Sistema 1, Milano'
            ],
            [
                'username' => 'admin.sicurezza',
                'email' => 'admin.sicurezza@homebanking.com',
                'first_name' => 'Laura',
                'last_name' => 'Sicurezza',
                'phone' => '+39 333 0000002',
                'address' => 'Via della Sicurezza 2, Roma'
            ],
            [
                'username' => 'admin.operazioni',
                'email' => 'admin.operazioni@homebanking.com',
                'first_name' => 'Giuseppe',
                'last_name' => 'Operazioni',
                'phone' => '+39 333 0000003',
                'address' => 'Via delle Operazioni 3, Napoli'
            ]
        ];

        foreach ($admins as $adminData) {
            $admin = User::firstOrCreate(
                ['username' => $adminData['username']],
                [
                    'email' => $adminData['email'],
                    'password' => Hash::make('admin123'),
                    'first_name' => $adminData['first_name'],
                    'last_name' => $adminData['last_name'],
                    'phone' => $adminData['phone'],
                    'address' => $adminData['address'],
                    'role' => 'admin',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (!$admin->hasRole('admin')) {
                $admin->assignRole('admin');
            }
        }

        // ========== DIPENDENTI ==========
        $employees = [
            [
                'username' => 'emp.sportello1',
                'email' => 'sportello1@homebanking.com',
                'first_name' => 'Anna',
                'last_name' => 'Sportello',
                'phone' => '+39 333 1111001',
                'address' => 'Via dello Sportello 10, Milano'
            ],
            [
                'username' => 'emp.consulente1',
                'email' => 'consulente1@homebanking.com',
                'first_name' => 'Roberto',
                'last_name' => 'Consulente',
                'phone' => '+39 333 1111002',
                'address' => 'Via dei Consulenti 15, Roma'
            ],
            [
                'username' => 'emp.assistenza1',
                'email' => 'assistenza1@homebanking.com',
                'first_name' => 'Sofia',
                'last_name' => 'Assistenza',
                'phone' => '+39 333 1111003',
                'address' => 'Via dell\'Assistenza 20, Torino'
            ],
            [
                'username' => 'emp.backoffice1',
                'email' => 'backoffice1@homebanking.com',
                'first_name' => 'Francesco',
                'last_name' => 'BackOffice',
                'phone' => '+39 333 1111004',
                'address' => 'Via del BackOffice 25, Bologna'
            ],
            [
                'username' => 'emp.controlli1',
                'email' => 'controlli1@homebanking.com',
                'first_name' => 'Elena',
                'last_name' => 'Controlli',
                'phone' => '+39 333 1111005',
                'address' => 'Via dei Controlli 30, Firenze'
            ]
        ];

        foreach ($employees as $empData) {
            $employee = User::firstOrCreate(
                ['username' => $empData['username']],
                [
                    'email' => $empData['email'],
                    'password' => Hash::make('employee123'),
                    'first_name' => $empData['first_name'],
                    'last_name' => $empData['last_name'],
                    'phone' => $empData['phone'],
                    'address' => $empData['address'],
                    'role' => 'employee',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (!$employee->hasRole('employee')) {
                $employee->assignRole('employee');
            }
        }

        // ========== CLIENTI ==========
        $clients = [
            [
                'username' => 'mario.rossi',
                'email' => 'mario.rossi@email.com',
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'phone' => '+39 333 2222001',
                'address' => 'Via Roma 123, 20100 Milano',
                'initial_balance' => 5000.00
            ],
            [
                'username' => 'giulia.bianchi',
                'email' => 'giulia.bianchi@email.com',
                'first_name' => 'Giulia',
                'last_name' => 'Bianchi',
                'phone' => '+39 333 2222002',
                'address' => 'Corso Italia 456, 10100 Torino',
                'initial_balance' => 3500.50
            ],
            [
                'username' => 'franco.verdi',
                'email' => 'franco.verdi@email.com',
                'first_name' => 'Franco',
                'last_name' => 'Verdi',
                'phone' => '+39 333 2222003',
                'address' => 'Piazza San Marco 789, 30100 Venezia',
                'initial_balance' => 7500.75
            ],
            [
                'username' => 'laura.neri',
                'email' => 'laura.neri@email.com',
                'first_name' => 'Laura',
                'last_name' => 'Neri',
                'phone' => '+39 333 2222004',
                'address' => 'Via del Corso 321, 00100 Roma',
                'initial_balance' => 2000.25
            ],
            [
                'username' => 'alessandro.ferrari',
                'email' => 'alessandro.ferrari@email.com',
                'first_name' => 'Alessandro',
                'last_name' => 'Ferrari',
                'phone' => '+39 333 2222005',
                'address' => 'Via Garibaldi 55, 50100 Firenze',
                'initial_balance' => 12000.00
            ],
            [
                'username' => 'chiara.colombo',
                'email' => 'chiara.colombo@email.com',
                'first_name' => 'Chiara',
                'last_name' => 'Colombo',
                'phone' => '+39 333 2222006',
                'address' => 'Via Milano 88, 40100 Bologna',
                'initial_balance' => 8500.30
            ],
            [
                'username' => 'davide.russo',
                'email' => 'davide.russo@email.com',
                'first_name' => 'Davide',
                'last_name' => 'Russo',
                'phone' => '+39 333 2222007',
                'address' => 'Corso Vittorio Emanuele 200, 80100 Napoli',
                'initial_balance' => 4200.15
            ],
            [
                'username' => 'federica.marino',
                'email' => 'federica.marino@email.com',
                'first_name' => 'Federica',
                'last_name' => 'Marino',
                'phone' => '+39 333 2222008',
                'address' => 'Via Dante 77, 90100 Palermo',
                'initial_balance' => 6800.90
            ],
            [
                'username' => 'luca.conti',
                'email' => 'luca.conti@email.com',
                'first_name' => 'Luca',
                'last_name' => 'Conti',
                'phone' => '+39 333 2222009',
                'address' => 'Via Manzoni 44, 70100 Bari',
                'initial_balance' => 15000.00
            ],
            [
                'username' => 'martina.greco',
                'email' => 'martina.greco@email.com',
                'first_name' => 'Martina',
                'last_name' => 'Greco',
                'phone' => '+39 333 2222010',
                'address' => 'Via Leopardi 33, 16100 Genova',
                'initial_balance' => 3200.45
            ],
            [
                'username' => 'stefano.ricci',
                'email' => 'stefano.ricci@email.com',
                'first_name' => 'Stefano',
                'last_name' => 'Ricci',
                'phone' => '+39 333 2222011',
                'address' => 'Via Verdi 111, 95100 Catania',
                'initial_balance' => 9750.60
            ],
            [
                'username' => 'valentina.bruno',
                'email' => 'valentina.bruno@email.com',
                'first_name' => 'Valentina',
                'last_name' => 'Bruno',
                'phone' => '+39 333 2222012',
                'address' => 'Via Pascoli 66, 09100 Cagliari',
                'initial_balance' => 5400.25
            ],
            [
                'username' => 'andrea.galli',
                'email' => 'andrea.galli@email.com',
                'first_name' => 'Andrea',
                'last_name' => 'Galli',
                'phone' => '+39 333 2222013',
                'address' => 'Via Carducci 22, 37100 Verona',
                'initial_balance' => 11200.80
            ],
            [
                'username' => 'silvia.fontana',
                'email' => 'silvia.fontana@email.com',
                'first_name' => 'Silvia',
                'last_name' => 'Fontana',
                'phone' => '+39 333 2222014',
                'address' => 'Via Petrarca 99, 35100 Padova',
                'initial_balance' => 7300.15
            ],
            [
                'username' => 'michele.barbieri',
                'email' => 'michele.barbieri@email.com',
                'first_name' => 'Michele',
                'last_name' => 'Barbieri',
                'phone' => '+39 333 2222015',
                'address' => 'Via Foscolo 145, 06100 Perugia',
                'initial_balance' => 18500.00
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

        $this->command->info('Demo data estesi creati con successo!');
        $this->command->info('');
        $this->command->info('=== AMMINISTRATORI ===');
        $this->command->info('admin (password: admin123)');
        foreach ($admins as $admin) {
            $this->command->info("- {$admin['username']} (password: admin123)");
        }
        
        $this->command->info('');
        $this->command->info('=== DIPENDENTI ===');
        foreach ($employees as $employee) {
            $this->command->info("- {$employee['username']} (password: employee123)");
        }
        
        $this->command->info('');
        $this->command->info('=== CLIENTI ===');
        $this->command->info('cliente1 (password: password123)');
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
     * Crea transazioni di esempio più elaborate
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
                'days_ago' => 1
            ],
            [
                'from' => $users[1],
                'to' => $users[2],
                'amount' => 75.50,
                'description' => 'Pagamento bolletta condominiale',
                'days_ago' => 2
            ],
            [
                'from' => $users[2],
                'to' => $users[0],
                'amount' => 200.00,
                'description' => 'Prestito personale',
                'days_ago' => 3
            ],
            [
                'from' => $users[0],
                'to' => $users[3],
                'amount' => 50.25,
                'description' => 'Regalo compleanno',
                'days_ago' => 4
            ],
            [
                'from' => $users[4],
                'to' => $users[1],
                'amount' => 300.00,
                'description' => 'Pagamento freelance',
                'days_ago' => 5
            ],
            [
                'from' => $users[3],
                'to' => $users[5],
                'amount' => 125.75,
                'description' => 'Spesa supermercato',
                'days_ago' => 6
            ],
            [
                'from' => $users[6],
                'to' => $users[2],
                'amount' => 400.00,
                'description' => 'Affitto mensile',
                'days_ago' => 7
            ],
            [
                'from' => $users[1],
                'to' => $users[7],
                'amount' => 85.30,
                'description' => 'Cena di lavoro',
                'days_ago' => 8
            ],
            [
                'from' => $users[8],
                'to' => $users[0],
                'amount' => 220.00,
                'description' => 'Vendita oggetto usato',
                'days_ago' => 9
            ],
            [
                'from' => $users[5],
                'to' => $users[9],
                'amount' => 180.50,
                'description' => 'Lezioni private',
                'days_ago' => 10
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