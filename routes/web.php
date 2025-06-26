<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Employee\EmployeeDashboardController;

// Route pubbliche
Route::view('/', 'index');

// Route protette per Admin
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gestione utenti
    Route::middleware(['permission:manage_users'])->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // Gestione account
    Route::middleware(['permission:manage_accounts'])->group(function () {
        Route::resource('accounts', AccountController::class);
    });
});

// Route protette per Employee
Route::middleware(['auth', 'verified', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    // Gestione transazioni
    Route::middleware(['permission:approve_transactions'])->group(function () {
        Route::get('/transactions/pending', [TransactionController::class, 'pending'])->name('transactions.pending');
        Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    });
});

// Route protette per Client
Route::middleware(['auth', 'verified', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    
    // Operazioni account
    Route::middleware(['permission:view_own_account'])->group(function () {
        Route::get('/account', [AccountController::class, 'show'])->name('account.show');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    });
    
    // Trasferimenti
    Route::middleware(['permission:make_transfers'])->group(function () {
        Route::get('/transfer', [TransferController::class, 'create'])->name('transfer.create');
        Route::post('/transfer', [TransferController::class, 'store'])->name('transfer.store');
    });
});