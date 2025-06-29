<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeClientAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminAssignmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Mostra la dashboard delle associazioni
     */
    public function index()
    {
        $employees = User::where('role', 'employee')
                        ->where('is_active', true)
                        ->withCount('assignedClients')
                        ->get();

        $clients = User::where('role', 'client')
                      ->where('is_active', true)
                      ->withCount('assignedEmployees')
                      ->get();

        $assignments = EmployeeClientAssignment::with(['employee', 'client', 'assignedBy'])
                                              ->active()
                                              ->latest()
                                              ->paginate(20);

        $stats = [
            'total_employees' => $employees->count(),
            'total_clients' => $clients->count(),
            'total_assignments' => $assignments->total(),
            'unassigned_clients' => $clients->where('assigned_employees_count', 0)->count(),
        ];

        return view('admin.assignments.index', compact('employees', 'clients', 'assignments', 'stats'));
    }

    /**
     * Mostra dettagli di un employee e i suoi clienti
     */
    public function showEmployee(User $employee)
    {
        if (!$employee->isEmployee()) {
            return redirect()->back()->withErrors(['error' => 'Utente non è un employee.']);
        }

        $assignedClients = $employee->assignedClients()->paginate(15);
        $availableClients = User::where('role', 'client')
                               ->where('is_active', true)
                               ->whereNotIn('id', $employee->assignedClients()->pluck('users.id'))
                               ->get();

        $stats = [
            'total_assigned' => $employee->assignedClients()->count(),
            'total_transactions' => $employee->getVisibleTransactions()->count(),
            'active_accounts' => $assignedClients->filter(fn($client) => $client->account && $client->account->is_active)->count(),
        ];

        return view('admin.assignments.employee', compact('employee', 'assignedClients', 'availableClients', 'stats'));
    }

    /**
     * Assegna un cliente a un employee
     */
    public function assignClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $employee = User::findOrFail($request->employee_id);
        $client = User::findOrFail($request->client_id);

        // Verifica che sia effettivamente un employee e un client
        if (!$employee->isEmployee()) {
            return back()->withErrors(['error' => 'L\'utente selezionato non è un employee.']);
        }

        if (!$client->isClient()) {
            return back()->withErrors(['error' => 'L\'utente selezionato non è un cliente.']);
        }

        // Verifica se l'associazione esiste già
        $existingAssignment = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                     ->where('client_id', $client->id)
                                                     ->first();

        if ($existingAssignment) {
            if ($existingAssignment->is_active) {
                return back()->withErrors(['error' => 'Il cliente è già assegnato a questo employee.']);
            } else {
                // Riattiva l'assegnazione esistente
                $existingAssignment->update([
                    'is_active' => true,
                    'assigned_by' => Auth::id(),
                    'notes' => $request->notes,
                    'assigned_at' => now(),
                ]);
            }
        } else {
            // Crea nuova assegnazione
            EmployeeClientAssignment::create([
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'assigned_by' => Auth::id(),
                'notes' => $request->notes,
                'is_active' => true,
            ]);
        }

        return back()->with('success', "Cliente {$client->full_name} assegnato con successo a {$employee->full_name}.");
    }

    /**
     * Rimuove l'assegnazione di un cliente da un employee
     */
    public function unassignClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $assignment = EmployeeClientAssignment::where('employee_id', $request->employee_id)
                                             ->where('client_id', $request->client_id)
                                             ->where('is_active', true)
                                             ->first();

        if (!$assignment) {
            return back()->withErrors(['error' => 'Assegnazione non trovata.']);
        }

        $assignment->update(['is_active' => false]);

        $employee = User::find($request->employee_id);
        $client = User::find($request->client_id);

        return back()->with('success', "Cliente {$client->full_name} rimosso da {$employee->full_name}.");
    }

    /**
     * Assegnazione multipla di clienti
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:users,id',
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $employee = User::findOrFail($request->employee_id);
        
        if (!$employee->isEmployee()) {
            return back()->withErrors(['error' => 'L\'utente selezionato non è un employee.']);
        }

        $assigned = 0;
        $skipped = 0;

        foreach ($request->client_ids as $clientId) {
            $client = User::find($clientId);
            
            if (!$client || !$client->isClient()) {
                $skipped++;
                continue;
            }

            // Verifica se esiste già
            $existingAssignment = EmployeeClientAssignment::where('employee_id', $employee->id)
                                                         ->where('client_id', $clientId)
                                                         ->first();

            if ($existingAssignment && $existingAssignment->is_active) {
                $skipped++;
                continue;
            }

            if ($existingAssignment) {
                // Riattiva
                $existingAssignment->update([
                    'is_active' => true,
                    'assigned_by' => Auth::id(),
                    'notes' => $request->notes,
                    'assigned_at' => now(),
                ]);
            } else {
                // Crea nuovo
                EmployeeClientAssignment::create([
                    'employee_id' => $employee->id,
                    'client_id' => $clientId,
                    'assigned_by' => Auth::id(),
                    'notes' => $request->notes,
                    'is_active' => true,
                ]);
            }

            $assigned++;
        }

        $message = "Assegnazione completata: {$assigned} clienti assegnati";
        if ($skipped > 0) {
            $message .= ", {$skipped} saltati (già assegnati o non validi)";
        }

        return back()->with('success', $message);
    }

    /**
     * Mostra statistiche avanzate
     */
    public function statistics()
    {
        $employeeStats = User::where('role', 'employee')
                            ->where('is_active', true)
                            ->withCount('assignedClients')
                            ->get()
                            ->map(function ($employee) {
                                $clientIds = $employee->assignedClients()->pluck('users.id');
                                $totalTransactions = 0;
                                $totalVolume = 0;

                                foreach ($clientIds as $clientId) {
                                    $client = User::find($clientId);
                                    if ($client && $client->account) {
                                        $transactions = $client->account->allTransactions()->get();
                                        $totalTransactions += $transactions->count();
                                        $totalVolume += $transactions->sum('amount');
                                    }
                                }

                                return [
                                    'employee' => $employee,
                                    'assigned_clients_count' => $employee->assigned_clients_count,
                                    'total_transactions' => $totalTransactions,
                                    'total_volume' => $totalVolume,
                                ];
                            });

        $unassignedClients = User::where('role', 'client')
                                ->where('is_active', true)
                                ->doesntHave('assignedEmployees')
                                ->get();

        return view('admin.assignments.statistics', compact('employeeStats', 'unassignedClients'));
    }
}