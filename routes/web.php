<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Homepage
Route::view('/', 'index');

// Pagina di selezione login
Route::view('/login', 'login');

// Form di login
Route::view('/login-cliente', 'login-cliente');
Route::view('/login-lavoratore', 'login-lavoratore');

// Gestione login cliente
Route::post('/login-cliente', function (Request $request) {
    $username = $request->input('username');
    $password = $request->input('password');
    
    // Cerca l'utente con il ruolo cliente
    $user = User::where('username', $username)
                ->where('role', 'client')
                ->where('is_active', true)
                ->first();
    
    if ($user && Hash::check($password, $user->password)) {
        Auth::login($user);
        return redirect('/dashboard-cliente')->with('success', 'Accesso effettuato con successo!');
    }
    
    return back()->withErrors(['login' => 'Credenziali non valide o account non attivo.']);
})->name('cliente.login.submit');

// Gestione login lavoratore (admin)
Route::post('/login-lavoratore', function (Request $request) {
    $matricola = $request->input('matricola');
    $password = $request->input('password');
    
    // Cerca l'utente con il ruolo admin
    $user = User::where('username', $matricola)
                ->where('role', 'admin')
                ->where('is_active', true)
                ->first();
    
    if ($user && Hash::check($password, $user->password)) {
        Auth::login($user);
        return redirect('/dashboard-admin')->with('success', 'Accesso effettuato con successo!');
    }
    
    return back()->withErrors(['login' => 'Credenziali non valide o account non attivo.']);
})->name('lavoratore.login.submit');

// Dashboard protected routes
Route::middleware(['auth'])->group(function () {
    
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

    // Main dashboard route with proper redirect logic
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
        
        // If user has no valid role, logout and redirect
        Auth::logout();
        return redirect('/login')->withErrors(['access' => 'Ruolo utente non riconosciuto.']);
    })->name('dashboard');
    
});

// Logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/')->with('success', 'Logout effettuato con successo.');
})->name('logout');