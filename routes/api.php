<?php
// Aggiungi questo al file routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllo autenticazione per JavaScript
Route::get('/auth-check', function (Request $request) {
    if (!Auth::check()) {
        return response()->json(['authenticated' => false], 401);
    }
    
    $user = Auth::user();
    
    // Verifica se l'utente è attivo
    if (!$user->is_active) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['authenticated' => false, 'reason' => 'inactive'], 401);
    }
    
    return response()->json([
        'authenticated' => true,
        'user' => [
            'id' => $user->id,
            'role' => $user->role,
            'name' => $user->full_name
        ]
    ]);
})->middleware('web');

// Pulizia sessione per JavaScript
Route::post('/session-cleanup', function (Request $request) {
    if ($request->input('action') === 'cleanup_session') {
        // Log della pulizia sessione
        \Log::info('Session cleanup requested via beacon', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Pulisci eventuali flag di sessione
        $request->session()->forget([
            'forced_logout_redirect',
            'forced_logout_reason', 
            'forced_logout_timestamp',
            'unauthorized_access_attempt',
            'back_button_blocked'
        ]);
    }
    
    return response()->json(['status' => 'ok']);
})->middleware('web');