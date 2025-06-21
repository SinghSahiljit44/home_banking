<?php

use Illuminate\Support\Facades\Route;
//use Illuminate\Support\Facades\DB;
//use App\Models\User;

Route::view('/', 'index');

Route::view('/login', 'login');

Route::view('/login-cliente', 'login-cliente');

Route::view('/login-lavoratore', 'login-lavoratore');

Route::post('/login-cliente', function (Request $request) {
// Autenticazione cliente (esempio base)
$email = $request->input('email');
$password = $request->input('password');
// TODO: aggiungi qui la logica vera
return redirect('/')->with('success', 'Cliente autenticato: ' . $email);
})->name('cliente.login.submit');

Route::post('/login-lavoratore', function (Request $request) {
// Autenticazione lavoratore (esempio base)
$username = $request->input('username');
$password = $request->input('password');
// TODO: aggiungi qui la logica vera
return redirect('/')->with('success', 'Lavoratore autenticato: ' . $username);
})->name('lavoratore.login.submit');