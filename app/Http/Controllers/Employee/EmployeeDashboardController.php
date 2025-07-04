<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\EmployeeClientAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Dashboard principale employee
     */
    public function index()
    {
        $employee = Auth::user();
        
        // Clienti assegnati usando query diretta per evitare problemi con le relazioni
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');
        
        $assignedClients = User::whereIn('id', $assignedClientIds)
                              ->with('account')
                              ->paginate(10);

        // Statistiche
        $stats = $this->getEmployeeStats($employee);

        // Transazioni recenti dei clienti assegnati
        $recentTransactions = $this->getRecentClientTransactions($employee);

        // Clienti con problemi/alert
        $clientAlerts = $this->getClientAlerts($employee);

        return view('employee.dashboard', compact(
            'assignedClients', 
            'stats', 
            'recentTransactions', 
            'clientAlerts'
        ));
    }

    /**
     * Lista completa clienti assegnati
     */
    public function clients(Request $request)
    {
        $employee = Auth::user();
        
        // Query diretta per evitare problemi con le relazioni
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');
        
        $query = User::whereIn('id', $assignedClientIds)->with('account');

        // Filtri
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('account_status')) {
            if ($request->get('account_status') === 'active') {
                $query->whereHas('account', function($q) {
                    $q->where('is_active', true);
                });
            } elseif ($request->get('account_status') === 'inactive') {
                $query->whereHas('account', function($q) {
                    $q->where('is_active', false);
                });
            } elseif ($request->get('account_status') === 'no_account') {
                $query->whereDoesntHave('account');
            }
        }

        $clients = $query->paginate(15)->withQueryString();

        return view('employee.clients.index', compact('clients'));
    }

    /**
     * Dettagli di un cliente specifico
     */
    public function showClient(User $client)
    {
        $employee = Auth::user();

        // Verifica che il cliente sia assegnato a questo employee usando query diretta
        $isAssigned = EmployeeClientAssignment::where('employee_id', $employee->id)
                                             ->where('client_id', $client->id)
                                             ->where('is_active', true)
                                             ->exists();

        if (!$isAssigned) {
            abort(403, 'Non hai accesso a questo cliente.');
        }

        $client->load(['account', 'securityQuestion']);

        // Statistiche del cliente
        $clientStats = null;
        if ($client->account) {
            $clientStats = [
                'total_transactions' => $client->account->allTransactions()->count(),
                'total_incoming' => $client->account->incomingTransactions()->where('status', 'completed')->sum('amount'),
                'total_outgoing' => $client->account->outgoingTransactions()->where('status', 'completed')->sum('amount'),
                'last_transaction' => $client->account->allTransactions()->first(),
                'current_balance' => $client->account->balance,
            ];
        }

        // Transazioni recenti
        $recentTransactions = $client->account 
            ? $client->account->allTransactions()->take(10)->get() 
            : collect();

        return view('employee.clients.show', compact('client', 'clientStats', 'recentTransactions'));
    }

    /**
     * Transazioni dei clienti assegnati
     */
    public function transactions(Request $request)
    {
        $employee = Auth::user();
        
        // Usa query diretta per evitare problemi con le relazioni
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');
        
        $accountIds = Account::whereIn('user_id', $assignedClientIds)->pluck('id');
        
        $query = Transaction::where(function($q) use ($accountIds) {
            $q->whereIn('from_account_id', $accountIds)
              ->orWhereIn('to_account_id', $accountIds);
        });

        // Filtri
        if ($request->filled('client_id')) {
            $client = User::find($request->get('client_id'));
            if ($client && $assignedClientIds->contains($client->id) && $client->account) {
                $query->where(function($q) use ($client) {
                    $q->where('from_account_id', $client->account->id)
                      ->orWhere('to_account_id', $client->account->id);
                });
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $transactions = $query->with(['fromAccount.user', 'toAccount.user'])
                             ->orderBy('created_at', 'desc')
                             ->paginate(20)
                             ->withQueryString();

        // Lista clienti per filtro
        $assignedClients = User::whereIn('id', $assignedClientIds)->get();

        return view('employee.transactions.index', compact('transactions', 'assignedClients'));
    }

    /**
     * Mostra dettagli di una transazione specifica (solo per clienti assegnati)
     */
    public function showTransactionDetails($id)
    {
        $employee = Auth::user();
        $transaction = Transaction::with(['fromAccount.user', 'toAccount.user'])->findOrFail($id);
        
        // Verifica che la transazione appartenga a un cliente assegnato
        $hasAccess = false;
        
        // Ottieni clienti assegnati
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');
        
        // Verifica se la transazione coinvolge un cliente assegnato
        if ($transaction->fromAccount && $transaction->fromAccount->user && 
            $assignedClientIds->contains($transaction->fromAccount->user_id)) {
            $hasAccess = true;
        }
        
        if ($transaction->toAccount && $transaction->toAccount->user && 
            $assignedClientIds->contains($transaction->toAccount->user_id)) {
            $hasAccess = true;
        }
        
        if (!$hasAccess) {
            abort(403, 'Non hai accesso a questa transazione');
        }
        
        // Prepara i dati per la vista
        $data = [
            'id' => $transaction->id,
            'reference_code' => $transaction->reference_code,
            'amount' => $transaction->amount,
            'formatted_amount' => number_format($transaction->amount, 2, ',', '.'),
            'type' => $transaction->type,
            'status' => $transaction->status,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at,
            'from_user' => $transaction->fromAccount ? $transaction->fromAccount->user->full_name : 'Sistema',
            'to_user' => $transaction->toAccount ? $transaction->toAccount->user->full_name : 'Esterno',
            'from_account' => $transaction->fromAccount ? $transaction->fromAccount->account_number : '-',
            'to_account' => $transaction->toAccount ? $transaction->toAccount->account_number : '-',
            'from_iban' => $transaction->fromAccount ? $transaction->fromAccount->iban : '-',
            'to_iban' => $transaction->toAccount ? $transaction->toAccount->iban : '-',
        ];
        
        // Se è una richiesta AJAX, restituisci solo l'HTML del modal
        if (request()->ajax() || request()->wantsJson()) {
            return view('employee.transactions.modal-details', compact('transaction', 'data'))->render();
        }
        
        // Per richieste normali, usa la vista completa
        return view('employee.transactions.show', compact('transaction', 'data'));
    }
    /**
     * Statistiche avanzate employee
     */
    public function statistics()
    {
        $employee = Auth::user();
        
        $stats = $this->getEmployeeStats($employee);
        
        // Statistiche per cliente usando query dirette
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');
        
        $clientStats = User::whereIn('id', $assignedClientIds)
                          ->with('account')
                          ->get()
                          ->map(function ($client) {
                              $clientData = [
                                  'client' => $client,
                                  'total_transactions' => 0,
                                  'total_volume' => 0,
                                  'last_activity' => null,
                                  'account_status' => $client->account ? ($client->account->is_active ? 'active' : 'inactive') : 'no_account'
                              ];

                              if ($client->account) {
                                  $transactions = $client->account->allTransactions()->get();
                                  $clientData['total_transactions'] = $transactions->count();
                                  $clientData['total_volume'] = $transactions->sum('amount');
                                  $clientData['last_activity'] = $transactions->first()?->created_at;
                              }

                              return $clientData;
                          });

        return view('employee.statistics', compact('stats', 'clientStats'));
    }

    /**
     * Calcola statistiche per l'employee usando query dirette
     */
    private function getEmployeeStats(User $employee): array
    {
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');

        $assignedClientsCount = $assignedClientIds->count();
        
        $activeAccountsCount = User::whereIn('id', $assignedClientIds)
                                  ->whereHas('account', function($q) {
                                      $q->where('is_active', true);
                                  })->count();

        $accountIds = Account::whereIn('user_id', $assignedClientIds)->pluck('id');
        
        $totalTransactions = 0;
        $totalVolume = 0;
        $totalBalance = 0;
        $transactionsToday = 0;

        if ($accountIds->isNotEmpty()) {
            $totalTransactions = Transaction::where(function($query) use ($accountIds) {
                $query->whereIn('from_account_id', $accountIds)
                      ->orWhereIn('to_account_id', $accountIds);
            })->count();

            $totalVolume = Transaction::where(function($query) use ($accountIds) {
                $query->whereIn('from_account_id', $accountIds)
                      ->orWhereIn('to_account_id', $accountIds);
            })->sum('amount');

            $totalBalance = Account::whereIn('id', $accountIds)->sum('balance');

            $transactionsToday = Transaction::where(function($query) use ($accountIds) {
                $query->whereIn('from_account_id', $accountIds)
                      ->orWhereIn('to_account_id', $accountIds);
            })->whereDate('created_at', today())->count();
        }

        return [
            'assigned_clients' => $assignedClientsCount,
            'active_accounts' => $activeAccountsCount,
            'total_transactions' => $totalTransactions,
            'total_volume' => $totalVolume,
            'total_balance' => $totalBalance,
            'transactions_today' => $transactionsToday,
            'average_balance_per_client' => $assignedClientsCount > 0 ? $totalBalance / $assignedClientsCount : 0,
        ];
    }

    /**
     * Ottiene transazioni recenti dei clienti assegnati
     */
    private function getRecentClientTransactions(User $employee)
    {
        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');
        
        $accountIds = Account::whereIn('user_id', $assignedClientIds)->pluck('id');
        
        return Transaction::where(function($query) use ($accountIds) {
                    $query->whereIn('from_account_id', $accountIds)
                          ->orWhereIn('to_account_id', $accountIds);
                })
                ->with(['fromAccount.user', 'toAccount.user'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
    }

    /**
     * Ottiene alert/problemi dei clienti usando query dirette
     */
    private function getClientAlerts(User $employee): array
    {
        $alerts = [];

        $assignedClientIds = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                    ->where('is_active', true)
                                                    ->pluck('client_id');

        $clients = User::whereIn('id', $assignedClientIds)->with('account')->get();

        foreach ($clients as $client) {
            // Cliente senza conto
            if (!$client->account) {
                $alerts[] = [
                    'type' => 'no_account',
                    'severity' => 'warning',
                    'client' => $client,
                    'message' => 'Cliente senza conto corrente'
                ];
                continue;
            }

            // Conto bloccato
            if (!$client->account->is_active) {
                $alerts[] = [
                    'type' => 'blocked_account',
                    'severity' => 'danger',
                    'client' => $client,
                    'message' => 'Conto corrente bloccato'
                ];
            }

            // Saldo basso
            if ($client->account->balance < 100) {
                $alerts[] = [
                    'type' => 'low_balance',
                    'severity' => 'warning',
                    'client' => $client,
                    'message' => 'Saldo molto basso (€' . number_format($client->account->balance, 2) . ')'
                ];
            }

            // Nessuna attività recente
            $lastTransaction = $client->account->allTransactions()->first();
            if (!$lastTransaction || $lastTransaction->created_at->lt(now()->subDays(30))) {
                $alerts[] = [
                    'type' => 'inactive',
                    'severity' => 'info',
                    'client' => $client,
                    'message' => 'Nessuna attività negli ultimi 30 giorni'
                ];
            }

            // Transazioni in sospeso
            $pendingTransactions = $client->account->allTransactions()
                                                  ->where('status', 'pending')
                                                  ->count();
            if ($pendingTransactions > 0) {
                $alerts[] = [
                    'type' => 'pending_transactions',
                    'severity' => 'warning',
                    'client' => $client,
                    'message' => "{$pendingTransactions} transazioni in sospeso"
                ];
            }
        }

        return $alerts;
    }
}