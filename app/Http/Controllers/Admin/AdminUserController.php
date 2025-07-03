<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Lista tutti gli utenti
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');

        $query = User::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->with('account')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role', 'status'));
    }

    /**
     * Mostra il form per creare un nuovo cliente
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Registra un nuovo cliente - FIXED
     */
    public function store(Request $request)
    {
        // VALIDAZIONE MIGLIORATA
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8', // CAMBIATO: password opzionale
            'role' => 'required|in:client,employee',
            'create_account' => 'nullable|boolean',
            'initial_balance' => 'nullable|numeric|min:0|max:1000000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Errore nella validazione dei dati.');
        }

        try {
            \DB::beginTransaction();

            // GENERA PASSWORD AUTOMATICAMENTE SE NON FORNITA
            $password = $request->filled('password') 
                ? $request->password 
                : $this->generateSecurePassword(12);

            // Crea l'utente
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($password),
                'role' => $request->role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $account = null;
            
            // Crea il conto se richiesto (solo per clienti)
            if ($request->boolean('create_account') && $request->role === 'client') {
                $initialBalance = floatval($request->initial_balance ?? 0);
                
                // PRIMA crea il conto con saldo 0
                $account = $this->createAccountForUser($user, 0);
                
                // POI se c'è un saldo iniziale, crea UNA SOLA transazione di deposito
                if ($initialBalance > 0) {
                    $this->transactionService->createDeposit(
                        $account, 
                        $initialBalance, 
                        'Deposito iniziale - Apertura conto'
                    );
                }
            }

            \DB::commit();

            // Messaggio di successo con password generata
            $successMessage = "Utente {$user->full_name} creato con successo.";
            if (!$request->filled('password')) {
                $successMessage .= " Password generata: {$password}";
            }
            if ($account) {
                $successMessage .= " Conto creato: {$account->account_number}";
            }

            return redirect()->route('admin.users.show', $user)
                ->with('success', $successMessage)
                ->with('generated_password', !$request->filled('password') ? $password : null);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Admin user creation failed:', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['general' => 'Errore durante la creazione dell\'utente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostra i dettagli di un utente
     */
    public function show(User $user)
    {
        $user->load(['account', 'securityQuestion']);
        
        // Statistiche transazioni se ha un conto
        $transactionStats = null;
        if ($user->account) {
            $transactionStats = [
                'total_transactions' => $user->account->allTransactions()->count(),
                'total_incoming' => $user->account->incomingTransactions()->where('status', 'completed')->sum('amount'),
                'total_outgoing' => $user->account->outgoingTransactions()->where('status', 'completed')->sum('amount'),
                'last_transaction' => $user->account->allTransactions()->first(),
            ];
        }

        return view('admin.users.show', compact('user', 'transactionStats'));
    }

    /**
     * Mostra il form per modificare un utente
     */
    public function edit(User $user)
    {
        // CONTROLLO: Admin non possono modificare altri admin
        $currentUser = Auth::user();
        if ($user->isAdmin() && $currentUser->id !== $user->id) {
            return redirect()->route('admin.users.index')
                ->withErrors(['error' => 'Non puoi modificare i dati di altri amministratori.']);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Aggiorna un utente - FIXED
     */
    public function update(Request $request, User $user)
    {
        // CONTROLLO: Admin non possono modificare altri admin
        $currentUser = Auth::user();
        if ($user->isAdmin() && $currentUser->id !== $user->id) {
            return redirect()->route('admin.users.index')
                ->withErrors(['error' => 'Non puoi modificare i dati di altri amministratori.']);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'reset_password' => 'nullable|boolean',
            'new_password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ];

            // Solo admin possono cambiare lo stato di attivazione di altri utenti
            if ($currentUser->isAdmin() && $currentUser->id !== $user->id) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            // Reset password se richiesto
            if ($request->boolean('reset_password') && $request->filled('new_password')) {
                $updateData['password'] = Hash::make($request->new_password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'Utente aggiornato con successo.');

        } catch (\Exception $e) {
            \Log::error('User update failed:', [
                'admin_id' => Auth::id(),
                'target_user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Errore durante l\'aggiornamento dell\'utente.'])->withInput();
        }
    }

    /**
     * Elimina un utente (soft delete) - FIXED
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // CONTROLLI DI SICUREZZA
        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Impossibile eliminare un amministratore.']);
        }

        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'Non puoi eliminare te stesso.']);
        }

        try {
            \DB::beginTransaction();

            // Salva dati per logging prima dell'eliminazione
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'account_balance' => $user->account ? $user->account->balance : 0,
                'account_number' => $user->account ? $user->account->account_number : null,
                'iban' => $user->account ? $user->account->iban : null,
            ];

            // 1. Elimina tutte le transazioni associate all'account
            if ($user->account) {
                // Elimina transazioni in entrata
                $user->account->incomingTransactions()->delete();
                
                // Elimina transazioni in uscita
                $user->account->outgoingTransactions()->delete();
                
                // Elimina l'account
                $user->account->delete();
            }

            // 2. Elimina le assegnazioni employee-client
            if ($user->isEmployee()) {
                $user->employeeAssignments()->delete();
            }
            
            if ($user->isClient()) {
                $user->clientAssignments()->delete();
            }

            // 4. Elimina le domande di sicurezza
            if ($user->securityQuestion) {
                $user->securityQuestion->delete();
            }

            // 5. Elimina definitivamente l'utente
            $user->delete();

            \DB::commit();

            // Log dell'eliminazione completa
            \Log::warning('User permanently deleted by admin:', [
                'admin_id' => $currentUser->id,
                'admin_name' => $currentUser->full_name,
                'deleted_user_data' => $userData,
                'deletion_type' => 'HARD_DELETE',
                'timestamp' => now()->toISOString(),
            ]);

            $message = "Utente {$userData['full_name']} eliminato definitivamente dal sistema.";
            if ($userData['account_balance'] > 0) {
                $message .= " Saldo di €" . number_format($userData['account_balance'], 2, ',', '.') . " eliminato.";
            }

            return redirect()->route('admin.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('User hard deletion failed:', [
                'admin_id' => $currentUser->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Errore durante l\'eliminazione dell\'utente: ' . $e->getMessage()]);
        }
    }

    /**
     * Crea un conto per l'utente
     */
    public function createAccount(User $user)
    {
        if ($user->account) {
            return back()->withErrors(['general' => 'L\'utente ha già un conto associato.']);
        }

        if (!$user->isClient()) {
            return back()->withErrors(['general' => 'Solo i clienti possono avere un conto corrente.']);
        }

        try {
            $account = $this->createAccountForUser($user, 0);
            
            return redirect()->route('admin.users.show', $user)
                ->with('success', 'Conto creato con successo per l\'utente.');

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante la creazione del conto.']);
        }
    }

    /**
     * Blocca/Sblocca un conto
     */
    public function toggleAccountStatus(User $user)
    {
        if (!$user->account) {
            return back()->withErrors(['general' => 'L\'utente non ha un conto associato.']);
        }

        $user->account->update([
            'is_active' => !$user->account->is_active
        ]);

        $status = $user->account->is_active ? 'sbloccato' : 'bloccato';
        
        return back()->with('success', "Conto {$status} con successo.");
    }

    /**
     * Deposita denaro sul conto dell'utente
     */
    public function deposit(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'description' => 'required|string|max:255',
        ]);

        if (!$user->account || !$user->account->is_active) {
            return back()->withErrors(['general' => 'Conto non disponibile per il deposito.']);
        }

        try {
            $this->transactionService->createDeposit(
                $user->account,
                $request->amount,
                $request->description
            );

            return back()->with('success', 'Deposito effettuato con successo.');

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante il deposito.']);
        }
    }

    /**
     * Blocca/Sblocca un utente (Admin può gestire tutti gli utenti) - FIXED
     */
    public function toggleUserStatus(User $user)
    {
        $currentUser = Auth::user();

        if ($user->isAdmin() && $user->id !== $currentUser->id) {
            return back()->withErrors(['error' => 'Non puoi modificare lo stato di altri amministratori.']);
        }

        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'Non puoi modificare il tuo stesso stato.']);
        }

        $oldStatus = $user->is_active;
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'attivato' : 'disattivato';
        
        // Log dell'operazione
        \Log::info('User status changed by admin:', [
            'admin_id' => $currentUser->id,
            'admin_name' => $currentUser->full_name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->full_name,
            'old_status' => $oldStatus,
            'new_status' => $user->is_active,
        ]);

        return back()->with('success', "Utente {$status} con successo.");
    }

    /**
     * Rimuove un utente (Admin può rimuovere tutti tranne admin) - FIXED
     */
    public function removeUser(User $user)
    {
        $currentUser = Auth::user();

        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Non puoi rimuovere un amministratore.']);
        }

        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'Non puoi rimuovere te stesso.']);
        }

        try {
            \DB::beginTransaction();

            // Salva dati per logging prima dell'eliminazione
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'account_balance' => $user->account ? $user->account->balance : 0,
                'account_number' => $user->account ? $user->account->account_number : null,
            ];

            // Elimina tutto come nel metodo destroy
            if ($user->account) {
                $user->account->incomingTransactions()->delete();
                $user->account->outgoingTransactions()->delete();
                $user->account->delete();
            }

            if ($user->isEmployee()) {
                $user->employeeAssignments()->delete();
            }
            
            if ($user->isClient()) {
                $user->clientAssignments()->delete();
            }

            $user->beneficiaries()->delete();

            if ($user->securityQuestion) {
                $user->securityQuestion->delete();
            }

            $user->delete();

            \DB::commit();

            // Log dell'operazione
            \Log::warning('User permanently removed by admin:', [
                'admin_id' => $currentUser->id,
                'admin_name' => $currentUser->full_name,
                'removed_user_data' => $userData,
                'removal_type' => 'HARD_DELETE',
                'timestamp' => now()->toISOString(),
            ]);

            $message = "Utente {$userData['full_name']} rimosso definitivamente dal sistema.";
            if ($userData['account_balance'] > 0) {
                $message .= " Saldo di €" . number_format($userData['account_balance'], 2, ',', '.') . " eliminato.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('User hard removal failed:', [
                'admin_id' => $currentUser->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Errore durante la rimozione dell\'utente.']);
        }
    }
    /**
     * Metodo privato per creare un conto - INVARIATO
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
     * Calcola i digit di controllo IBAN - INVARIATO
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

    /**
     * NUOVO: Genera una password sicura
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%&*';

        // Assicurati che ci sia almeno un carattere per tipo
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Riempi il resto
        $allChars = $lowercase . $uppercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Mescola i caratteri
        return str_shuffle($password);
    }
}