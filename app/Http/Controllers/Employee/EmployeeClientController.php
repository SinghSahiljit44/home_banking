<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeClientController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Registra un nuovo cliente
     */
    public function create()
    {
        return view('employee.clients.create');
    }

    /**
     * AGGIORNATO: Registra un nuovo cliente e lo auto-assegna
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'create_account' => 'boolean',
            'initial_balance' => 'nullable|numeric|min:0',
            'auto_assign' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $employee = Auth::user();
            
            // Genera password temporanea
            $temporaryPassword = Str::random(12);

            // Crea l'utente
            $client = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($temporaryPassword),
                'role' => 'client',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // RIMOSSO assignRole perché non è configurato nel progetto
            // $client->assignRole('client');

            // Auto-assegna il cliente a questo employee se richiesto
            if ($request->boolean('auto_assign')) {
                \App\Models\EmployeeClientAssignment::create([
                    'employee_id' => $employee->id,
                    'client_id' => $client->id,
                    'assigned_by' => $employee->id, // Employee che crea si auto-assegna
                    'notes' => 'Auto-assegnato durante la creazione',
                    'is_active' => true,
                ]);
            }

            // Crea il conto se richiesto
            if ($request->boolean('create_account')) {
                $account = $this->createAccountForUser($client, $request->initial_balance ?? 0);
                
                if ($request->initial_balance > 0) {
                    $this->transactionService->createDeposit(
                        $account, 
                        $request->initial_balance, 
                        "Deposito iniziale - Creato da {$employee->full_name}"
                    );
                }
            }

            return redirect()->route('employee.clients.show', $client)
                           ->with('success', "Cliente creato con successo. Password temporanea: {$temporaryPassword}")
                           ->with('temp_password', $temporaryPassword);

        } catch (\Exception $e) {
            \Log::error('Employee client creation failed:', [
                'employee_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Errore durante la creazione del cliente.'])->withInput();
        }
    }

    /**
     * Modifica un cliente assegnato
     */
    public function edit(User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        return view('employee.clients.edit', compact('client'));
    }

    /**
     * Aggiorna un cliente assegnato
     */
    public function update(Request $request, User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $client->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('employee.clients.show', $client)
                        ->with('success', 'Cliente aggiornato con successo.');
    }

    /**
     * Reset password di un cliente assegnato
     */
    public function resetPassword(User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        // Genera nuova password temporanea
        $newPassword = Str::random(12);

        $client->update([
            'password' => Hash::make($newPassword)
        ]);

        // Log dell'operazione
        \Log::info('Employee password reset:', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'client_id' => $client->id,
            'client_name' => $client->full_name,
        ]);

        return back()->with('success', "Password resettata con successo. Nuova password: {$newPassword}")
                    ->with('temp_password', $newPassword);
    }

    /**
     * Deposita denaro sul conto di un cliente ASSEGNATO
     */
    public function deposit(Request $request, User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'description' => 'required|string|max:255',
        ]);

        if (!$client->account || !$client->account->is_active) {
            return back()->withErrors(['general' => 'Conto non disponibile per il deposito.']);
        }

        try {
            $description = $request->description . " - Operatore: {$employee->full_name}";
            
            $this->transactionService->createDeposit(
                $client->account,
                $request->amount,
                $description
            );

            return back()->with('success', 'Deposito effettuato con successo.');

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante il deposito.']);
        }
    }

    /**
     * Crea un bonifico per conto del cliente
     */
    public function makeTransfer(Request $request, User $client)
    {
        $employee = Auth::user();

        if (!$employee->canMakeTransfersForClient($client)) {
            abort(403, 'Non puoi fare bonifici per questo cliente.');
        }

        $validator = Validator::make($request->all(), [
            'recipient_iban' => 'required|string|min:15|max:34',
            'amount' => 'required|numeric|min:0.01|max:50000',
            'description' => 'required|string|max:255',
            'beneficiary_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$client->account || !$client->account->is_active) {
            return back()->withErrors(['general' => 'Conto del cliente non disponibile.']);
        }

        if (!$client->account->hasSufficientBalance($request->amount)) {
            return back()->withErrors(['amount' => 'Saldo insufficiente.']);
        }

        try {
            $description = $request->description . " - Operatore: {$employee->full_name}";
            
            $result = $this->transactionService->processBonifico(
                $client->account,
                strtoupper(str_replace(' ', '', $request->recipient_iban)),
                $request->amount,
                $description,
                $request->beneficiary_name
            );

            if ($result['success']) {
                return back()->with('success', "Bonifico completato con successo. Codice: {$result['reference_code']}");
            } else {
                return back()->withErrors(['general' => $result['message']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante l\'elaborazione del bonifico.']);
        }
    }

    /**
     * Crea un conto per il cliente
     */
    public function createAccount(User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        if ($client->account) {
            return back()->withErrors(['general' => 'Il cliente ha già un conto associato.']);
        }

        try {
            $account = $this->createAccountForUser($client, 0);
            
            return back()->with('success', 'Conto creato con successo per il cliente.');

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante la creazione del conto.']);
        }
    }

    /**
     * AGGIORNATO: Blocca/sblocca un cliente assegnato
     */
    public function toggleClientStatus(User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        $oldStatus = $client->is_active;
        $client->update(['is_active' => !$client->is_active]);

        $status = $client->is_active ? 'attivato' : 'disattivato';

        // Log dell'operazione
        \Log::info('Client status changed by employee:', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'client_id' => $client->id,
            'client_name' => $client->full_name,
            'old_status' => $oldStatus,
            'new_status' => $client->is_active,
        ]);

        return back()->with('success', "Cliente {$status} con successo.");
    }

    /**
     * AGGIORNATO: Rimuove un cliente assegnato (solo disattivazione)
     */
    public function removeClient(User $client)
    {
        $employee = Auth::user();

        if (!$employee->canManageClient($client)) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        try {
            // Disattiva il cliente
            $client->update(['is_active' => false]);

            // Disattiva anche il conto se presente
            if ($client->account) {
                $client->account->update(['is_active' => false]);
            }

            // Log dell'operazione
            \Log::info('Client removed by employee:', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'client_id' => $client->id,
                'client_name' => $client->full_name,
            ]);

            return back()->with('success', "Cliente {$client->full_name} disattivato con successo.");

        } catch (\Exception $e) {
            \Log::error('Client removal failed:', [
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Errore durante la disattivazione del cliente.']);
        }
    }

    /**
     * Metodo privato per creare un conto
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
}