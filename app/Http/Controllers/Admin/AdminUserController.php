<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->middleware('auth');
        $this->transactionService = $transactionService;
    }

    /**
     * Lista tutti gli utenti
     */
    public function index(Request $request)
    {
        $this->authorize('manage_users');

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
        $this->authorize('manage_users');
        return view('admin.users.create');
    }

    /**
     * Registra un nuovo cliente
     */
    public function store(Request $request)
    {
        $this->authorize('manage_users');

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8',
            'role' => 'required|in:client,employee',
            'create_account' => 'boolean',
            'initial_balance' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Crea l'utente
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Assegna il ruolo Spatie
            $user->assignRole($request->role);

            // Crea il conto se richiesto (solo per clienti)
            if ($request->create_account && $request->role === 'client') {
                $account = $this->createAccountForUser($user, $request->initial_balance ?? 0);
                
                if ($request->initial_balance > 0) {
                    $this->transactionService->createDeposit(
                        $account, 
                        $request->initial_balance, 
                        'Deposito iniziale - Apertura conto'
                    );
                }
            }

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'Utente creato con successo.');

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante la creazione dell\'utente.'])->withInput();
        }
    }

    /**
     * Mostra i dettagli di un utente
     */
    public function show(User $user)
    {
        $this->authorize('manage_users');
        
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
        $this->authorize('manage_users');
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Aggiorna un utente
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('manage_users');

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'reset_password' => 'boolean',
            'new_password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active'),
        ];

        // Reset password se richiesto
        if ($request->reset_password && $request->new_password) {
            $updateData['password'] = Hash::make($request->new_password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utente aggiornato con successo.');
    }

    /**
     * Elimina un utente (soft delete)
     */
    public function destroy(User $user)
    {
        $this->authorize('manage_users');

        if ($user->isAdmin()) {
            return back()->withErrors(['general' => 'Impossibile eliminare un amministratore.']);
        }

        try {
            // Disattiva l'utente invece di eliminarlo definitivamente
            $user->update([
                'is_active' => false,
                'email' => $user->email . '_deleted_' . time(),
                'username' => $user->username . '_deleted_' . time(),
            ]);

            // Disattiva anche il conto se presente
            if ($user->account) {
                $user->account->update(['is_active' => false]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'Utente disattivato con successo.');

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Errore durante l\'eliminazione dell\'utente.']);
        }
    }

    /**
     * Crea un conto per l'utente
     */
    public function createAccount(User $user)
    {
        $this->authorize('manage_users');

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
        $this->authorize('manage_users');

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
        $this->authorize('manage_users');

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
}