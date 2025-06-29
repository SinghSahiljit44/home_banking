<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:employee');
    }

    /**
     * Dashboard principale employee
     */
    public function index()
    {
        $employee = Auth::user();
        
        // Clienti assegnati
        $assignedClients = $employee->assignedClients()
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
        
        $query = $employee->assignedClients()->with('account');

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

        // Verifica che il cliente sia assegnato a questo employee
        if (!$employee->canManageClient($client)) {
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
        
        $query = $employee->getVisibleTransactions();

        // Filtri
        if ($request->filled('client_id')) {
            $client = User::find($request->get('client_id'));
            if ($client && $employee->canViewClientTransactions($client) && $client->account) {
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
        $assignedClients = $employee->assignedClients;

        return view('employee.transactions.index', compact('transactions', 'assignedClients'));
    }

    /**
     * Statistiche avanzate employee
     */
    public function statistics()
    {
        $employee = Auth::user();
        
        $stats = $this->getEmployeeStats($employee);
        
        // Statistiche per cliente
        $clientStats = $employee->assignedClients()
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
     * Calcola statistiche per l'employee
     */
    private function getEmployeeStats(User $employee): array
    {
        $assignedClientsCount = $employee->assignedClients()->count();
        $activeAccountsCount = $employee->assignedClients()
                                       ->whereHas('account', function($q) {
                                           $q->where('is_active', true);
                                       })->count();

        $totalTransactions = 0;
        $totalVolume = 0;
        $totalBalance = 0;
        $transactionsToday = 0;

        foreach ($employee->assignedClients as $client) {
            if ($client->account) {
                $transactions = $client->account->allTransactions();
                $totalTransactions += $transactions->count();
                $totalVolume += $transactions->sum('amount');
                $totalBalance += $client->account->balance;
                $transactionsToday += $transactions->whereDate('created_at', today())->count();
            }
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
        return $employee->getVisibleTransactions()
                        ->with(['fromAccount.user', 'toAccount.user'])
                        ->orderBy('created_at', 'desc')
                        ->take(10)
                        ->get();
    }

    /**
     * Ottiene alert/problemi dei clienti
     */
    private function getClientAlerts(User $employee): array
    {
        $alerts = [];

        foreach ($employee->assignedClients as $client) {
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