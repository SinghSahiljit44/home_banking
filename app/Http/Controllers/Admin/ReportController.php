<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard dei report
     */
    public function index()
    {
        // Statistiche generali
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_accounts' => Account::count(),
            'active_accounts' => Account::where('is_active', true)->count(),
            'total_balance' => Account::sum('balance'),
            'total_transactions' => Transaction::count(),
            'transactions_today' => Transaction::whereDate('created_at', today())->count(),
            'volume_today' => Transaction::whereDate('created_at', today())->sum('amount'),
        ];

        // Transazioni per mese (ultimi 12 mesi)
        $dailyTransactions = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top transazioni
        $topTransactions = Transaction::with(['fromAccount.user', 'toAccount.user'])
            ->orderBy('amount', 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.index', compact('stats', 'dailyTransactions', 'topTransactions'));
    }

    /**
     * Report dettagliato transazioni
     */
    public function transactions(Request $request)
    {
        $query = Transaction::with(['fromAccount.user', 'toAccount.user']);

        // Filtri
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->min_amount) {
            $query->where('amount', '>=', $request->min_amount);
        }
        
        if ($request->max_amount) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reports.transactions', compact('transactions'));
    }

    /**
     * Report utenti
     */
    public function users(Request $request)
    {        
        $query = User::with('account');

        // Filtri
        if ($request->role) {
            $query->where('role', $request->role);
        }
        
        if ($request->status) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }
        
        if ($request->has_account) {
            if ($request->has_account === 'yes') {
                $query->has('account');
            } elseif ($request->has_account === 'no') {
                $query->doesntHave('account');
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reports.users', compact('users'));
    }
}
