<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $this->authorize('manage_users');
        
        $stats = [
            'total_users' => User::count(),
            'total_accounts' => Account::count(),
            'total_transactions' => Transaction::count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}