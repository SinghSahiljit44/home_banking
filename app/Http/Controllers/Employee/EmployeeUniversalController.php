<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeUniversalController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Mostra tutti i clienti per operazioni di deposito
     */
    public function showAllClients(Request $request)
    {
        $employee = Auth::user();
        
        $search = $request->input('search');
        $status = $request->input('status');

        $query = User::where('role', 'client')
                    ->with('account');

        // Filtri
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } else {
            $query->where('is_active', true);
        }

        $clients = $query->orderBy('last_name')
                         ->orderBy('first_name')
                         ->paginate(20)
                         ->withQueryString();

        $stats = [
            'total_clients' => User::where('role', 'client')->count(),
            'active_clients' => User::where('role', 'client')->where('is_active', true)->count(),
            'clients_with_account' => User::where('role', 'client')
                                         ->whereHas('account')
                                         ->count(),
            'assigned_to_me' => $employee->assignedClients()->count(),
        ];

        return view('employee.universal.clients', compact('clients', 'stats', 'search', 'status'));
    }

    /**
     * Deposita denaro sul conto di qualsiasi cliente
     */
    public function depositToAnyClient(Request $request, User $client)
    {
        $employee = Auth::user();

        // Verifica che sia un cliente valido
        if (!$client->isClient()) {
            return back()->withErrors(['general' => 'L\'utente selezionato non è un cliente.']);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:100000',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$client->account || !$client->account->is_active) {
            return back()->withErrors(['general' => 'Conto del cliente non disponibile per il deposito.']);
        }

        try {
            $description = $request->description . " - Operatore: {$employee->full_name}";
            
            $this->transactionService->createDeposit(
                $client->account,
                $request->amount,
                $description
            );

            \Log::info('Employee universal deposit:', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'amount' => $request->amount,
                'description' => $description,
            ]);

            return back()->with('success', "Deposito di €{$request->amount} effettuato con successo per {$client->full_name}.");

        } catch (\Exception $e) {
            \Log::error('Employee universal deposit failed:', [
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Errore durante il deposito.']);
        }
    }

    /**
     * Mostra dettagli di un cliente specifico (per depositi)
     */
    public function showClientForDeposit(User $client)
    {
        $employee = Auth::user();

        if (!$client->isClient()) {
            abort(404, 'Cliente non trovato.');
        }

        $client->load(['account', 'securityQuestion']);

        // Statistiche del cliente
        $clientStats = null;
        if ($client->account) {
            $clientStats = [
                'total_transactions' => $client->account->allTransactions()->count(),
                'total_incoming' => $client->account->incomingTransactions()->where('status', 'completed')->sum('amount'),
                'current_balance' => $client->account->balance,
                'last_transaction' => $client->account->allTransactions()->first(),
            ];
        }

        // Verifica se è un cliente assegnato all'employee
        $isAssignedClient = $employee->canManageClient($client);

        // Transazioni recenti (solo se assegnato)
        $recentTransactions = $isAssignedClient && $client->account 
            ? $client->account->allTransactions()->take(5)->get() 
            : collect();

        return view('employee.universal.client-detail', compact(
            'client', 
            'clientStats', 
            'recentTransactions', 
            'isAssignedClient'
        ));
    }

    /**
     * Cerca clienti per autocompletamento
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $clients = User::where('role', 'client')
                      ->where('is_active', true)
                      ->where(function($query) use ($search) {
                          $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                      })
                      ->select('id', 'first_name', 'last_name', 'email', 'username')
                      ->limit(10)
                      ->get();

        return response()->json($clients->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->full_name,
                'email' => $client->email,
                'username' => $client->username,
                'has_account' => $client->account ? true : false,
            ];
        }));
    }

    public function withdrawalFromAnyClient(Request $request, User $client)
    {
        $employee = Auth::user();

        // Verifica che sia un cliente valido
        if (!$client->isClient()) {
            return back()->withErrors(['general' => 'L\'utente selezionato non è un cliente.']);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:100000',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$client->account || !$client->account->is_active) {
            return back()->withErrors(['general' => 'Conto del cliente non disponibile per il prelievo.']);
        }

        if (!$client->account->hasSufficientBalance($request->amount)) {
            return back()->withErrors(['amount' => 'Saldo insufficiente per il prelievo richiesto.']);
        }

        try {
            $description = $request->description . " - Operatore: {$employee->full_name}";
            
            $result = $this->transactionService->createWithdrawal(
                $client->account,
                $request->amount,
                $description
            );

            // Log dell'operazione
            \Log::info('Employee universal withdrawal:', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'amount' => $request->amount,
                'description' => $description,
                'transaction_id' => $result['transaction']->id ?? 'N/A',
            ]);

            if ($result['success']) {
                return back()->with('success', "Prelievo di €{$request->amount} effettuato con successo per {$client->full_name}.");
            } else {
                return back()->withErrors(['general' => $result['message']]);
            }

        } catch (\Exception $e) {
            \Log::error('Employee universal withdrawal failed:', [
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Errore durante il prelievo.']);
        }
    }
}
