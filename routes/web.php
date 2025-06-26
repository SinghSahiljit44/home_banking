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

// Dashboard temporanee (da implementare)
Route::get('/dashboard-cliente', function (): RedirectResponse|View {
    if (!Auth::check() || !Auth::user()->isClient()) {
        return redirect('/login')->withErrors(['access' => 'Accesso non autorizzato.']);
    }
    
    return view('dashboard-cliente');
})->name('dashboard.cliente');

Route::get('/dashboard-admin', function () {
    if (!Auth::check() || !Auth::user()->isAdmin()) {
        return redirect('/login')->withErrors(['access' => 'Accesso non autorizzato.']);
    }
    
    return view('dashboard-admin');
})->name('dashboard.admin');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/')->with('success', 'Logout effettuato con successo.');
})->name('logout');