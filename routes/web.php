<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Client\TransferController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\SecurityQuestionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminAssignmentsController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\PasswordRecoveryController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeClientController;

// Homepage
Route::view('/', 'index');

// FUNZIONE HELPER 
if (!function_exists('clearSecurityData')) {
    function clearSecurityData(Request $request): void
    {
        $securityKeys = [
            'forced_logout_redirect',
            'forced_logout_reason',
            'security_message',
            '_previous',
            '_flash',
            'errors'
        ];
        
        foreach ($securityKeys as $key) {
            $request->session()->forget($key);
        }
        
        \Log::info('Security cleanup performed:', [
            'ip' => $request->ip(),
            'keys_cleared' => count($securityKeys)
        ]);
    }
}

// Routes per ospiti (non autenticati)
Route::middleware(['web'])->group(function () {
    
    Route::view('/login', 'login')->name('login');
    Route::view('/login-cliente', 'login-cliente')->name('login.cliente');
    Route::view('/login-lavoratore', 'login-lavoratore')->name('login.lavoratore');
    
    // Login cliente 
    Route::post('/login-cliente', function (Request $request) {

        clearSecurityData($request);
        $request->session()->regenerate(true);
        
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');
        
        $user = User::where('username', $username)
                    ->where('role', 'client')
                    ->where('is_active', true)
                    ->first();
        
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            
            \Log::info('Client login successful:', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
            ]);
            
            return redirect('/dashboard-cliente')->with('success', 'Accesso effettuato con successo!');
        }
        
        \Log::warning('Client login failed:', [
            'username' => $username,
            'ip' => $request->ip(),
        ]);
        
        return back()->withErrors(['login' => 'Credenziali non valide o account non attivo.'])->withInput($request->only('username'));
        
    })->middleware('guest')->name('cliente.login.submit');

    // Login lavoratore 
    Route::post('/login-lavoratore', function (Request $request) {

        clearSecurityData($request);
        $request->session()->regenerate(true);
        
        $request->validate([
            'matricola' => 'required|string',
            'password' => 'required|string',
        ]);

        $matricola = $request->input('matricola');
        $password = $request->input('password');
        
        $user = User::where('username', $matricola)
                    ->whereIn('role', ['admin', 'employee'])
                    ->where('is_active', true)
                    ->first();
        
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            
            \Log::info('Worker login successful:', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip' => $request->ip(),
            ]);
            
            if ($user->isAdmin()) {
                return redirect('/dashboard-admin')->with('success', 'Accesso effettuato con successo!');
            } else {
                return redirect('/dashboard-employee')->with('success', 'Accesso effettuato con successo!');
            }
        }
        
        \Log::warning('Worker login failed:', [
            'matricola' => $matricola,
            'ip' => $request->ip(),
        ]);
        
        return back()->withErrors(['login' => 'Credenziali non valide o account non attivo.'])->withInput($request->only('matricola'));
        
    })->middleware('guest')->name('lavoratore.login.submit');
});

// DASHBOARD ROUTES 
Route::middleware(['web', 'auth'])->group(function () {
    
    // Dashboard cliente
    Route::get('/dashboard-cliente', function (Request $request) {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            \Log::warning('Wrong role access to client dashboard:', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'expected_role' => 'client',
                'ip' => $request->ip(),
            ]);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->withErrors(['access' => 'Ruolo non autorizzato per questa area.']);
        }
        
        return view('dashboard-cliente');
    })->name('dashboard.cliente');

    // Dashboard admin
    Route::get('/dashboard-admin', function (Request $request) {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            \Log::warning('Wrong role access to admin dashboard:', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'expected_role' => 'admin',
                'ip' => $request->ip(),
            ]);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->withErrors(['access' => 'Ruolo non autorizzato per questa area.']);
        }
        
        return view('dashboard-admin');
    })->name('dashboard.admin');

    // Dashboard employee
    Route::get('/dashboard-employee', function (Request $request) {
        $user = Auth::user();
        
        if (!$user->isEmployee()) {
            \Log::warning('Wrong role access to employee dashboard:', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'expected_role' => 'employee',
                'ip' => $request->ip(),
            ]);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->withErrors(['access' => 'Ruolo non autorizzato per questa area.']);
        }
        
        return view('dashboard-employee');
    })->name('dashboard.employee');

    // Main dashboard redirect
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        if ($user->isAdmin()) {
            return redirect()->route('dashboard.admin');
        }
        
        if ($user->isClient()) {
            return redirect()->route('dashboard.cliente');
        }
        
        if ($user->isEmployee()) {
            return redirect()->route('dashboard.employee');
        }
        
        Auth::logout();
        return redirect('/login')->withErrors(['access' => 'Ruolo utente non riconosciuto.']);
    })->name('dashboard');
});

// ROUTES CON MIDDLEWARE DI SICUREZZA
Route::middleware(['web', 'auth', 'prevent.back', 'security.session'])->group(function () {
    
    // ========== CLIENT ROUTES ==========
    
    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
        
        // BONIFICI
        Route::prefix('transfer')->name('transfer.')->group(function () {
            Route::get('/create', [TransferController::class, 'create'])->name('create');
            Route::post('/store', [TransferController::class, 'store'])->name('store');
            Route::post('/confirm', [TransferController::class, 'confirm'])->name('confirm');
            Route::get('/cancel', [TransferController::class, 'cancel'])->name('cancel');
        });

        // DOMANDE DI SICUREZZA
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/questions', [SecurityQuestionController::class, 'index'])->name('questions');
            Route::post('/questions', [SecurityQuestionController::class, 'store'])->name('questions.store');
            Route::get('/verify', [SecurityQuestionController::class, 'verify'])->name('verify');
            Route::post('/verify', [SecurityQuestionController::class, 'checkAnswer'])->name('verify.check');
            Route::delete('/questions', [SecurityQuestionController::class, 'destroy'])->name('questions.destroy');
        });

        // PROFILO
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
            Route::post('/update', [ProfileController::class, 'update'])->name('update');
            Route::post('/confirm-changes', [ProfileController::class, 'confirmChanges'])->name('confirm-changes');
            Route::get('/cancel-changes', [ProfileController::class, 'cancelChanges'])->name('cancel-changes');
            
            // Cambio password
            Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change-password');
            Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password.store');
            Route::post('/confirm-password-change', [ProfileController::class, 'confirmPasswordChange'])->name('confirm-password-change');
        });

        // CONTO E MOVIMENTI
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/', [AccountController::class, 'show'])->name('show');
            Route::get('/export-csv', [AccountController::class, 'exportCsv'])->name('export-csv');
            Route::get('/transaction/{id}', [AccountController::class, 'showTransaction'])->name('transaction.show');
        });
    });

    // ========== ADMIN ROUTES ==========

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard admin con controller
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // PROFILO ADMIN
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
            Route::post('/update', [ProfileController::class, 'update'])->name('update');
            Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change-password');
            Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password.store');
        });
        
        // GESTIONE UTENTI
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
            
            // Azioni speciali
            Route::post('/{user}/create-account', [AdminUserController::class, 'createAccount'])->name('create-account');
            Route::post('/{user}/toggle-account', [AdminUserController::class, 'toggleAccountStatus'])->name('toggle-account');
            Route::post('/{user}/deposit', [AdminUserController::class, 'deposit'])->name('deposit');
            Route::post('/{user}/toggle-status', [AdminUserController::class, 'toggleUserStatus'])->name('toggle-status');
            Route::post('/{user}/remove', [AdminUserController::class, 'removeUser'])->name('remove');
            Route::post('/{user}/withdrawal', [AdminUserController::class, 'withdrawal'])->name('withdrawal');
            Route::get('/{user}/withdrawal-form', [AdminUserController::class, 'showCreateWithdrawalForm'])->name('withdrawal-form');
        });

        // GESTIONE ASSOCIAZIONI EMPLOYEE-CLIENT
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [AdminAssignmentsController::class, 'index'])->name('index');
            Route::get('/employee/{employee}', [AdminAssignmentsController::class, 'showEmployee'])->name('employee');
            Route::post('/assign', [AdminAssignmentsController::class, 'assignClient'])->name('assign');
            Route::post('/unassign', [AdminAssignmentsController::class, 'unassignClient'])->name('unassign');
            Route::post('/bulk-assign', [AdminAssignmentsController::class, 'bulkAssign'])->name('bulk-assign');
            Route::get('/statistics', [AdminAssignmentsController::class, 'statistics'])->name('statistics');
        });

        // RECUPERO CREDENZIALI
        Route::prefix('password-recovery')->name('password-recovery.')->group(function () {
            Route::get('/', [PasswordRecoveryController::class, 'index'])->name('index');
            Route::post('/generate', [PasswordRecoveryController::class, 'generatePassword'])->name('generate');
            Route::post('/reset-username', [PasswordRecoveryController::class, 'resetUsername'])->name('reset-username');
            Route::post('/unlock-account', [PasswordRecoveryController::class, 'unlockAccount'])->name('unlock-account');
            Route::post('/bulk-reset', [PasswordRecoveryController::class, 'bulkReset'])->name('bulk-reset');
            Route::get('/search-users', [PasswordRecoveryController::class, 'searchUsers'])->name('search-users');
        });

        // REPORT E STATISTICHE
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/transactions', [App\Http\Controllers\Admin\ReportController::class, 'transactions'])->name('transactions');
            Route::get('/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
        });

        // GESTIONE TRANSAZIONI
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [AdminTransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [AdminTransactionController::class, 'show'])->name('show');
            Route::post('/{transaction}/approve', [AdminTransactionController::class, 'approve'])->name('approve');
            Route::post('/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('reject');
            Route::post('/{transaction}/reverse', [AdminTransactionController::class, 'reverse'])->name('reverse');
            
            // BONIFICI PER CONTO DEI CLIENTI
            Route::get('/create-transfer/{client}', [AdminTransactionController::class, 'showCreateTransferForm'])->name('create-transfer-form');
            Route::post('/create-transfer/{client}', [AdminTransactionController::class, 'createTransferForClient'])->name('create-transfer');
        });

        // GESTIONE CONTI
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', function () {
                $accounts = App\Models\Account::with('user')->paginate(20);
                return view('admin.accounts.index', compact('accounts'));
            })->name('index');
            
            Route::get('/{account}', function (App\Models\Account $account) {
                return view('admin.accounts.show', compact('account'));
            })->name('show');
            
            Route::post('/{account}/freeze', function (App\Models\Account $account) {
                $account->update(['is_active' => false]);
                return back()->with('success', 'Conto bloccato con successo.');
            })->name('freeze');
            
            Route::post('/{account}/unfreeze', function (App\Models\Account $account) {
                $account->update(['is_active' => true]);
                return back()->with('success', 'Conto sbloccato con successo.');
            })->name('unfreeze');
        });
    });

    // ========== EMPLOYEE ROUTES ==========
    
    Route::middleware(['role:employee'])->prefix('employee')->name('employee.')->group(function () {
        
        // DASHBOARD
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/statistics', [EmployeeDashboardController::class, 'statistics'])->name('statistics');

        // PROFILO EMPLOYEE
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
            Route::post('/update', [ProfileController::class, 'update'])->name('update');
            Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change-password');
            Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password.store');
        });

        // GESTIONE CLIENTI ASSEGNATI
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [EmployeeDashboardController::class, 'clients'])->name('index');
            Route::get('/create', [EmployeeClientController::class, 'create'])->name('create');
            Route::post('/', [EmployeeClientController::class, 'store'])->name('store');
            Route::get('/{client}', [EmployeeDashboardController::class, 'showClient'])->name('show');
            Route::get('/{client}/edit', [EmployeeClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [EmployeeClientController::class, 'update'])->name('update');
            
            // Azioni per clienti ASSEGNATI
            Route::post('/{client}/reset-password', [EmployeeClientController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{client}/transfer', [EmployeeClientController::class, 'makeTransfer'])->name('transfer');
            Route::post('/{client}/create-account', [EmployeeClientController::class, 'createAccount'])->name('create-account');
            Route::post('/{client}/toggle-status', [EmployeeClientController::class, 'toggleClientStatus'])->name('toggle-status');
            Route::post('/{client}/remove', [EmployeeClientController::class, 'removeClient'])->name('remove');

            // DEPOSITI/PRELIEVI per clienti assegnati
            Route::post('/{client}/deposit', [EmployeeClientController::class, 'deposit'])->name('deposit');
            Route::post('/{client}/withdrawal', [EmployeeClientController::class, 'withdrawal'])->name('withdrawal');
        });

        // DEPOSITI UNIVERSALI (tutti i clienti)
        Route::prefix('universal')->name('universal.')->group(function () {
            Route::get('/clients', [App\Http\Controllers\Employee\EmployeeUniversalController::class, 'showAllClients'])->name('clients');
            Route::post('/clients/{client}/deposit', [App\Http\Controllers\Employee\EmployeeUniversalController::class, 'depositToAnyClient'])->name('deposit');
            Route::post('/clients/{client}/withdrawal', [App\Http\Controllers\Employee\EmployeeUniversalController::class, 'withdrawalFromAnyClient'])->name('withdrawal');
            Route::get('/clients/{client}/detail', [App\Http\Controllers\Employee\EmployeeUniversalController::class, 'showClientForDeposit'])->name('client-detail');
            Route::get('/search-clients', [App\Http\Controllers\Employee\EmployeeUniversalController::class, 'searchClients'])->name('search-clients');
        });

        // TRANSAZIONI CLIENTI ASSEGNATI
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [EmployeeDashboardController::class, 'transactions'])->name('index');
            Route::get('/details/{id}', [EmployeeDashboardController::class, 'showTransactionDetails'])->name('details');
            Route::get('/show/{id}', [EmployeeDashboardController::class, 'showTransactionDetails'])->name('show');
        });

        // RECUPERO CREDENZIALI CLIENTI ASSEGNATI
        Route::prefix('password-recovery')->name('password-recovery.')->group(function () {
            Route::get('/', [PasswordRecoveryController::class, 'index'])->name('index');
            Route::post('/generate', [PasswordRecoveryController::class, 'generatePassword'])->name('generate');
            Route::post('/unlock-account', [PasswordRecoveryController::class, 'unlockAccount'])->name('unlock-account');
            Route::get('/search-users', [PasswordRecoveryController::class, 'searchUsers'])->name('search-users');
        });
    });
});

// ========== LOGOUT ==========
Route::post('/logout', function (Request $request) {
    $user = Auth::user();
    
    \Log::info('User logout:', [
        'user_id' => $user?->id,
        'username' => $user?->username,
        'role' => $user?->role,
        'ip' => $request->ip(),
    ]);
    
    Auth::logout();
    clearSecurityData($request);
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    $response = redirect('/')->with('success', 'Logout effettuato con successo.');
    
    // Rimuovi cookie remember_me
    if ($request->hasCookie(Auth::getRecallerName())) {
        $response = $response->withCookie(cookie()->forget(Auth::getRecallerName()));
    }
    
    return $response;
})->name('logout');