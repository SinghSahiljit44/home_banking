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

// Homepage
Route::view('/', 'index');

// Routes per ospiti (non autenticati)
Route::middleware('guest')->group(function () {
    Route::view('/login', 'login');
    Route::view('/login-cliente', 'login-cliente');
    Route::view('/login-lavoratore', 'login-lavoratore');

    // Form di registrazione (opzionale)
    Route::view('/register', 'register')->name('register.form');
    
    // Password Reset Routes (se non gestite da Fortify)
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

    // Gestione registrazione
    Route::post('/register', function (Request $request) {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'is_active' => false, // Richiede attivazione da admin
        ]);

        return redirect('/login')->with('success', 'Registrazione completata! Il tuo account sarà attivato dall\'amministratore.');
    })->name('register');

    // Gestione login cliente
    Route::post('/login-cliente', function (Request $request) {
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
            return redirect()->intended('/dashboard-cliente')->with('success', 'Accesso effettuato con successo!');
        }
        
        return back()->withErrors(['login' => 'Credenziali non valide o account non attivo.'])->withInput($request->only('username'));
    })->name('cliente.login.submit');

    // Gestione login lavoratore (admin/employee)
    Route::post('/login-lavoratore', function (Request $request) {
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
            
            if ($user->isAdmin()) {
                return redirect()->intended('/dashboard-admin')->with('success', 'Accesso effettuato con successo!');
            } else {
                return redirect()->intended('/employee/dashboard')->with('success', 'Accesso effettuato con successo!');
            }
        }
        
        return back()->withErrors(['login' => 'Credenziali non valide o account non attivo.'])->withInput($request->only('matricola'));
    })->name('lavoratore.login.submit');
});

// Routes protette (utenti autenticati)
Route::middleware(['auth'])->group(function () {
    
    // ========== DASHBOARD ROUTES ==========
    
    // Dashboard cliente
    Route::get('/dashboard-cliente', function () {
        $user = Auth::user();
        
        if (!$user || !$user->isClient()) {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Accesso non autorizzato.']);
        }
        
        return view('dashboard-cliente');
    })->name('dashboard.cliente');

    // Dashboard admin
    Route::get('/dashboard-admin', function () {
        $user = Auth::user();
        
        if (!$user || !$user->isAdmin()) {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Accesso non autorizzato.']);
        }
        
        return view('dashboard-admin');
    })->name('dashboard.admin');

    // Dashboard employee
    Route::get('/employee/dashboard', function () {
        $user = Auth::user();
        
        if (!$user || !$user->isEmployee()) {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Accesso non autorizzato.']);
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

    // ========== CLIENT ROUTES ==========
    
    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
        
        // BONIFICI
        Route::prefix('transfer')->name('transfer.')->group(function () {
            Route::get('/create', [TransferController::class, 'create'])->name('create');
            Route::post('/store', [TransferController::class, 'store'])->name('store');
            Route::post('/confirm', [TransferController::class, 'confirm'])->name('confirm');
            Route::get('/cancel', [TransferController::class, 'cancel'])->name('cancel');
        });
        
        //NOTIFICHE
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Client\NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/mark-as-read', [App\Http\Controllers\Client\NotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('/mark-all-read', [App\Http\Controllers\Client\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [App\Http\Controllers\Client\NotificationController::class, 'destroy'])->name('destroy');
        });

        // BENEFICIARI
        Route::prefix('beneficiaries')->name('beneficiaries.')->group(function () {
            Route::get('/', [App\Http\Controllers\Client\BeneficiaryController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Client\BeneficiaryController::class, 'store'])->name('store');
            Route::put('/{beneficiary}', [App\Http\Controllers\Client\BeneficiaryController::class, 'update'])->name('update');
            Route::post('/{beneficiary}/toggle-favorite', [App\Http\Controllers\Client\BeneficiaryController::class, 'toggleFavorite'])->name('toggle-favorite');
            Route::delete('/{beneficiary}', [App\Http\Controllers\Client\BeneficiaryController::class, 'destroy'])->name('destroy');
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

        // SUPPORTO E ASSISTENZA
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', function () {
                return view('client.support.index');
            })->name('index');
            
            Route::get('/contact', function () {
                return view('client.support.contact');
            })->name('contact');
            
            Route::post('/contact', function (Illuminate\Http\Request $request) {
                // Implementa invio richiesta supporto
                return back()->with('success', 'Richiesta inviata con successo. Ti contatteremo presto.');
            })->name('contact.store');
        });

        // IMPOSTAZIONI
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', function () {
                return view('client.settings.index');
            })->name('index');
            
            Route::get('/security', function () {
                return view('client.settings.security');
            })->name('security');
            
            Route::get('/preferences', function () {
                return view('client.settings.preferences');
            })->name('preferences');
        });
    });

    // ========== ADMIN ROUTES ==========
    
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard admin con controller
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
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
        });

        // REPORT E STATISTICHE
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/transactions', [App\Http\Controllers\Admin\ReportController::class, 'transactions'])->name('transactions');
            Route::get('/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
            Route::get('/export/transactions', [App\Http\Controllers\Admin\ReportController::class, 'exportTransactions'])->name('export.transactions');
        });

        // GESTIONE TRANSAZIONI
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', function () {
                return view('admin.transactions.index');
            })->name('index');
            
            Route::get('/{transaction}', function ($transaction) {
                return view('admin.transactions.show', compact('transaction'));
            })->name('show');
            
            Route::post('/{transaction}/approve', function ($transaction) {
                // Implementa approvazione transazione
                return back()->with('success', 'Transazione approvata.');
            })->name('approve');
            
            Route::post('/{transaction}/reject', function ($transaction) {
                // Implementa rifiuto transazione
                return back()->with('success', 'Transazione rifiutata.');
            })->name('reject');
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

        // AUDIT E LOG
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/', function () {
                $logs = App\Models\ActivityLog::with('user')->latest()->paginate(50);
                return view('admin.audit.index', compact('logs'));
            })->name('index');
            
            Route::get('/user/{user}', function (App\Models\User $user) {
                $logs = $user->activityLogs()->latest()->paginate(50);
                return view('admin.audit.user', compact('user', 'logs'));
            })->name('user');
        });

        // IMPOSTAZIONI SISTEMA
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', function () {
                return view('admin.settings.index');
            })->name('index');
            
            Route::get('/limits', function () {
                return view('admin.settings.limits');
            })->name('limits');
            
            Route::get('/notifications', function () {
                return view('admin.settings.notifications');
            })->name('notifications');
            
            Route::get('/security', function () {
                return view('admin.settings.security');
            })->name('security');
        });
    });

    // ========== EMPLOYEE ROUTES ==========
    
    Route::middleware(['role:employee'])->prefix('employee')->name('employee.')->group(function () {
        // Routes per dipendenti (da implementare se necessario)
        Route::get('/dashboard', function () {
            return view('employee.dashboard');
        })->name('dashboard');
    });
});

// ========== LOGOUT ==========
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/')->with('success', 'Logout effettuato con successo.');
})->name('logout');

// ========== JETSTREAM ROUTES (se necessarie) ==========
// Queste routes sono gestite automaticamente da Jetstream
// ma le manteniamo per compatibilità