<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Account;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminTransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Lista tutte le transazioni del sistema
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');
        $status = $request->input('status');
        $clientId = $request->input('client_id');

        $query = Transaction::with(['fromAccount.user', 'toAccount.user']);

        // Filtri di ricerca
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_code', 'like', "%{$search}%")
                  ->orWhereHas('fromAccount.user', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('toAccount.user', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($clientId) {
            $client = User::find($clientId);
            if ($client && $client->account) {
                $query->where(function($q) use ($client) {
                    $q->where('from_account_id', $client->account->id)
                      ->orWhere('to_account_id', $client->account->id);
                });
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Lista clienti per filtro
        $clients = User::where('role', 'client')->where('is_active', true)->get();

        // Statistiche
        $stats = [
            'total_transactions' => Transaction::count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'completed_transactions' => Transaction::where('status', 'completed')->count(),
            'failed_transactions' => Transaction::where('status', 'failed')->count(),
            'total_volume' => Transaction::where('status', 'completed')->sum('amount'),
            'volume_today' => Transaction::where('status', 'completed')
                                        ->whereDate('created_at', today())
                                        ->sum('amount'),
        ];

        return view('admin.transactions.index', compact(
            'transactions', 
            'clients', 
            'stats',
            'search',
            'dateFrom',
            'dateTo',
            'type',
            'status',
            'clientId'
        ));
    }

    /**
     * Mostra dettagli di una transazione specifica
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['fromAccount.user', 'toAccount.user']);
        
        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Forza il completamento di una transazione in sospeso
     */
    public function approve(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo le transazioni in sospeso possono essere approvate.']);
        }

        $transaction->update(['status' => 'completed']);

        // Log dell'operazione
        \Log::info('Transaction approved by admin:', [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->full_name,
            'transaction_id' => $transaction->id,
            'reference_code' => $transaction->reference_code,
        ]);

        return back()->with('success', 'Transazione approvata e completata con successo.');
    }

    /**
     * Rifiuta una transazione in sospeso
     */
    public function reject(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo le transazioni in sospeso possono essere rifiutate.']);
        }

        // Se era una transazione in uscita, rimborsa l'importo al conto di origine
        if ($transaction->from_account_id && $transaction->fromAccount) {
            $transaction->fromAccount->increment('balance', $transaction->amount);
        }

        $transaction->update(['status' => 'failed']);

        // Log dell'operazione
        \Log::info('Transaction rejected by admin:', [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->full_name,
            'transaction_id' => $transaction->id,
            'reference_code' => $transaction->reference_code,
        ]);

        return back()->with('success', 'Transazione rifiutata. Importo rimborsato se necessario.');
    }

    /**
     * Crea un bonifico per conto di un cliente (ADMIN può fare bonifici per tutti) - AGGIORNATO
     */
    public function createTransferForClient(Request $request, User $client)
    {
        $validator = Validator::make($request->all(), [
            'recipient_iban' => 'required|string|min:15|max:34',
            'amount' => 'required|numeric|min:0.01|max:100000',
            'description' => 'required|string|max:255',
            'beneficiary_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$client->isClient()) {
            return back()->withErrors(['error' => 'L\'utente selezionato non è un cliente.']);
        }

        if (!$client->account || !$client->account->is_active) {
            return back()->withErrors(['error' => 'Conto del cliente non disponibile.']);
        }

        if (!$client->account->hasSufficientBalance($request->amount)) {
            return back()->withErrors(['amount' => 'Saldo insufficiente sul conto del cliente.']);
        }

        try {
            $description = $request->description . " - Operazione Admin: " . Auth::user()->full_name;
            
            // Salva il saldo prima della transazione
            $balanceBeforeTransaction = $client->account->balance;
            
            $result = $this->transactionService->processBonifico(
                $client->account,
                strtoupper(str_replace(' ', '', $request->recipient_iban)),
                $request->amount,
                $description,
                $request->beneficiary_name
            );

            // Log dell'operazione
            \Log::info('Admin created transfer for client:', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->full_name,
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'amount' => $request->amount,
                'recipient_iban' => $request->recipient_iban,
                'reference_code' => $result['reference_code'] ?? 'N/A',
            ]);

            if ($result['success']) {
                // MODIFICA PRINCIPALE: Invece di tornare alla pagina utente, mostra la pagina di successo
                return view('admin.transactions.transfer-success', [
                    'client' => $client,
                    'transaction' => $result['transaction'],
                    'reference_code' => $result['reference_code'],
                    'amount' => $request->amount,
                    'recipient_iban' => $request->recipient_iban,
                    'beneficiary_name' => $request->beneficiary_name,
                    'description' => $description,
                    'new_balance' => $client->account->fresh()->balance,
                    'previous_balance' => $balanceBeforeTransaction
                ]);
            } else {
                return back()->withErrors(['general' => $result['message']]);
            }

        } catch (\Exception $e) {
            \Log::error('Admin transfer creation failed:', [
                'admin_id' => Auth::id(),
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Errore durante la creazione del bonifico.']);
        }
    }

    /**
     * Mostra form per creare bonifico per cliente
     */
    public function showCreateTransferForm(User $client)
    {
        if (!$client->isClient() || !$client->account) {
            return redirect()->route('admin.users.show', $client)
                ->withErrors(['error' => 'Cliente non ha un conto disponibile.']);
        }

        return view('admin.transactions.create-transfer', compact('client'));
    }

    /**
     * Storna una transazione completata (solo admin)
     */
    public function reverse(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return back()->withErrors(['error' => 'Solo le transazioni completate possono essere stornate.']);
        }

        try {
            // Crea transazione di storno
            $reverseTransaction = Transaction::create([
                'from_account_id' => $transaction->to_account_id,
                'to_account_id' => $transaction->from_account_id,
                'amount' => $transaction->amount,
                'type' => 'transfer_out', // Invertiamo il tipo
                'description' => "STORNO - " . $transaction->description,
                'reference_code' => 'REV' . strtoupper(uniqid()),
                'status' => 'completed'
            ]);

            // Aggiorna i saldi
            if ($transaction->fromAccount) {
                $transaction->fromAccount->increment('balance', $transaction->amount);
            }
            if ($transaction->toAccount) {
                $transaction->toAccount->decrement('balance', $transaction->amount);
            }

            // Segna la transazione originale come stornata
            $transaction->update([
                'description' => $transaction->description . ' [STORNATA]'
            ]);

            // Log dell'operazione
            \Log::info('Transaction reversed by admin:', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->full_name,
                'original_transaction_id' => $transaction->id,
                'reverse_transaction_id' => $reverseTransaction->id,
            ]);

            return back()->with('success', 'Transazione stornata con successo.');

        } catch (\Exception $e) {
            \Log::error('Transaction reverse failed:', [
                'admin_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Errore durante lo storno della transazione.']);
        }
    }

    /**
     * Blocca/Sblocca un utente (Admin può gestire account utenti)
     */
    public function toggleUserStatus(User $user)
    {
        if ($user->isAdmin() && $user->id !== Auth::id()) {
            return back()->withErrors(['error' => 'Non puoi modificare lo stato di altri amministratori.']);
        }

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Non puoi modificare il tuo stesso stato.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'attivato' : 'disattivato';
        
        // Log dell'operazione
        \Log::info('User status changed by admin:', [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->full_name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->full_name,
            'new_status' => $user->is_active,
        ]);

        return back()->with('success', "Utente {$status} con successo.");
    }

    /**
     * Reset completo credenziali utente (Admin può fare per tutti)
     */
    public function resetUserCredentials(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'new_username' => 'nullable|string|max:50|unique:users,username,' . $user->id,
            'new_password' => 'nullable|string|min:8',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $currentUser = Auth::user();

        // PROTEZIONE RINFORZATA: Admin non possono modificare credenziali di altri admin
        if ($user->isAdmin() && $user->id !== $currentUser->id) {
            return back()->withErrors(['error' => 'Non puoi modificare le credenziali di altri amministratori.']);
        }

        $changes = [];

        try {
            // Cambia username se fornito
            if ($request->filled('new_username') && $request->new_username !== $user->username) {
                $oldUsername = $user->username;
                $user->username = $request->new_username;
                $changes[] = "Username: {$oldUsername} → {$request->new_username}";
            }

            // Cambia password se fornita
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
                $changes[] = "Password modificata";
            }

            if (empty($changes)) {
                return back()->withErrors(['error' => 'Nessuna modifica specificata.']);
            }

            $user->save();

            \Log::info('User credentials reset by admin:', [
                'admin_id' => $currentUser->id,
                'admin_name' => $currentUser->full_name,
                'target_user_id' => $user->id,
                'target_user_name' => $user->full_name,
                'changes' => $changes,
                'reason' => $request->reason,
            ]);

            $message = "Credenziali aggiornate: " . implode(', ', $changes);
            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Admin credentials reset failed:', [
                'admin_id' => $currentUser->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Errore durante l\'aggiornamento delle credenziali.']);
        }
    }
    
    public function exportCsv(Request $request)
    {
        $query = Transaction::with(['fromAccount.user', 'toAccount.user']);

        // Applica gli stessi filtri della vista index
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'admin_transactions_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'ID',
                'Data/Ora',
                'Conto Origine',
                'Utente Origine',
                'Conto Destinazione',
                'Utente Destinazione',
                'Importo (EUR)',
                'Tipo',
                'Descrizione',
                'Codice Riferimento',
                'Stato'
            ], ';');

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->created_at->format('d/m/Y H:i:s'),
                    $transaction->fromAccount ? $transaction->fromAccount->account_number : 'Sistema',
                    $transaction->fromAccount ? $transaction->fromAccount->user->full_name : 'Sistema',
                    $transaction->toAccount ? $transaction->toAccount->account_number : 'Esterno',
                    $transaction->toAccount ? $transaction->toAccount->user->full_name : 'Esterno',
                    number_format($transaction->amount, 2, ',', ''),
                    $transaction->type,
                    $transaction->description,
                    $transaction->reference_code,
                    $transaction->status
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}