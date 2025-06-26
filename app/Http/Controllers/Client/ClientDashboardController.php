<?php
// app/Http/Controllers/Client/ClientDashboardController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $account = $user->account;
        
        if (!$account) {
            return redirect()->route('client.account.create');
        }
        
        $recentTransactions = $account->allTransactions()->limit(10)->get();
        
        return view('client.dashboard', compact('account', 'recentTransactions'));
    }
}