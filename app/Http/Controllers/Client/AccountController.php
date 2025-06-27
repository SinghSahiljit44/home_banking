<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccountController extends Controller
{
    /**
     * Mostra saldo e movimenti del conto corrente
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $account = $user->account;

        if (!$account) {
            return redirect()->route('dashboard.cliente')
                ->withErrors(['account' => 'Nessun conto associato al tuo profilo.']);
        }

        // Parametri per filtri
        $perPage = $request->input('per_page', 10);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');
        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');

        // Query base per le transazioni
        $query = $account->allTransactions();

        // Applica filtri
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($minAmount) {
            $query->where('amount', '>=', $minAmount);
        }

        if ($maxAmount) {
            $query->where('amount', '<=', $maxAmount);
        }

        // Paginazione
        $transactions = $query->paginate($perPage)->withQueryString();

        // Statistiche del periodo
        $stats = $this->calculateAccountStats($account, $dateFrom, $dateTo);

        return view('client.account.show', compact(
            'account',
            'transactions',
            'stats',
            'dateFrom',
            'dateTo',
            'type',
            'minAmount',
            'maxAmount',
            'perPage'
        ));
    }

    /**
     * Esporta movimenti in CSV
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $account = $user->account;

        if (!$account) {
            return redirect()->back()->withErrors(['account' => 'Nessun conto trovato.']);
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');

        // Query per le transazioni da esportare
        $query = $account->allTransactions();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $transactions = $query->get();

        // Genera il file CSV
        $filename = 'movimenti_' . $account->account_number . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions, $account) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'Data',
                'Tipo',
                'Descrizione',
                'Importo',
                'Saldo',
                'Stato',
                'Riferimento'
            ], ';');

            $runningBalance = $account->balance;
            
            foreach ($transactions->reverse() as $transaction) {
                $amount = $transaction->from_account_id === $account->id ? -$transaction->amount : $transaction->amount;
                
                fputcsv($file, [
                    $transaction->created_at->format('d/m/Y H:i:s'),
                    $this->getTransactionTypeLabel($transaction->type),
                    $transaction->description,
                    number_format($amount, 2, ',', ''),
                    number_format($runningBalance, 2, ',', ''),
                    $this->getTransactionStatusLabel($transaction->status),
                    $transaction->reference_code
                ], ';');
                
                $runningBalance -= $amount;
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mostra dettagli di una singola transazione
     */
    public function showTransaction($id)
    {
        $user = Auth::user();
        $account = $user->account;

        if (!$account) {
            return redirect()->route('dashboard.cliente')
                ->withErrors(['account' => 'Nessun conto trovato.']);
        }

        $transaction = Transaction::where(function($query) use ($account) {
            $query->where('from_account_id', $account->id)
                  ->orWhere('to_account_id', $account->id);
        })->findOrFail($id);

        return view('client.account.transaction-detail', compact('transaction', 'account'));
    }

    /**
     * Calcola statistiche del conto per il periodo specificato
     */
    private function calculateAccountStats($account, $dateFrom = null, $dateTo = null)
    {
        $query = $account->allTransactions();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->get();

        $stats = [
            'total_transactions' => $transactions->count(),
            'total_incoming' => 0,
            'total_outgoing' => 0,
            'total_deposits' => 0,
            'total_withdrawals' => 0,
            'average_transaction' => 0,
            'largest_transaction' => 0,
        ];

        foreach ($transactions as $transaction) {
            if ($transaction->to_account_id === $account->id) {
                // Entrata
                $stats['total_incoming'] += $transaction->amount;
                if ($transaction->type === 'deposit') {
                    $stats['total_deposits'] += $transaction->amount;
                }
            } else {
                // Uscita
                $stats['total_outgoing'] += $transaction->amount;
                if ($transaction->type === 'withdrawal') {
                    $stats['total_withdrawals'] += $transaction->amount;
                }
            }

            if ($transaction->amount > $stats['largest_transaction']) {
                $stats['largest_transaction'] = $transaction->amount;
            }
        }

        if ($stats['total_transactions'] > 0) {
            $stats['average_transaction'] = ($stats['total_incoming'] + $stats['total_outgoing']) / $stats['total_transactions'];
        }

        return $stats;
    }

    /**
     * Ottieni l'etichetta del tipo di transazione
     */
    private function getTransactionTypeLabel($type)
    {
        $types = [
            'transfer_in' => 'Bonifico Ricevuto',
            'transfer_out' => 'Bonifico Inviato',
            'deposit' => 'Deposito',
            'withdrawal' => 'Prelievo',
        ];

        return $types[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Ottieni l'etichetta dello stato della transazione
     */
    private function getTransactionStatusLabel($status)
    {
        $statuses = [
            'pending' => 'In Elaborazione',
            'completed' => 'Completato',
            'failed' => 'Fallito',
        ];

        return $statuses[$status] ?? ucfirst($status);
    }
}