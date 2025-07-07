@extends('layouts.bootstrap')

@section('title', 'Dashboard Employee')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-user-tie me-2"></i>Dashboard Employee</h2>
                    <p class="text-muted">Benvenuto, {{ Auth::user()->full_name }}</p>
                </div>
                <div>
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->full_name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="fas fa-user me-2"></i>Profilo
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Menu Principale -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-transparent border-primary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>I Miei Clienti</h5>
                    <p class="small text-muted">Gestisci i clienti assegnati a te</p>
                    <a href="{{ route('employee.clients.index') }}" class="btn btn-primary">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-transparent border-success h-100">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-3x text-success mb-3"></i>
                    <h5>Operazioni Universali</h5>
                    <p class="small text-muted">Depositi e prelievi per tutti i clienti</p>
                    <a href="{{ route('employee.universal.clients') }}" class="btn btn-success">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-transparent border-info h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-3x text-info mb-3"></i>
                    <h5>Transazioni</h5>
                    <p class="small text-muted">Monitora le transazioni</p>
                    <a href="{{ route('employee.transactions.index') }}" class="btn btn-info">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-transparent border-warning h-100">
                <div class="card-body text-center">
                    <i class="fas fa-key fa-3x text-warning mb-3"></i>
                    <h5>Recupero Credenziali</h5>
                    <p class="small text-muted">Reset password clienti</p>
                    <a href="{{ route('employee.password-recovery.index') }}" class="btn btn-warning">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiche Rapide -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Le Tue Statistiche</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @php
                                // Calcolo sicuro del numero di clienti assegnati
                                $assignedClientsCount = \App\Models\EmployeeClientAssignment::where('employee_id', Auth::id())
                                                       ->where('is_active', true)
                                                       ->count();
                            @endphp
                            <h4 class="text-primary">{{ $assignedClientsCount }}</h4>
                            <p class="mb-0">Clienti Assegnati</p>
                        </div>
                        <div class="col-md-3 text-center">
                            @php
                                // Calcolo sicuro dei conti attivi
                                $activeAccountsCount = \App\Models\User::whereIn('id', function($query) {
                                    $query->select('client_id')
                                          ->from('employee_client_assignments')
                                          ->where('employee_id', Auth::id())
                                          ->where('is_active', true);
                                })
                                ->whereHas('account', function($q) { 
                                    $q->where('is_active', true); 
                                })->count();
                            @endphp
                            <h4 class="text-success">{{ $activeAccountsCount }}</h4>
                            <p class="mb-0">Conti Attivi</p>
                        </div>
                        <div class="col-md-3 text-center">
                            @php
                                // Calcolo sicuro del saldo totale gestito
                                $totalBalance = \App\Models\Account::whereIn('user_id', function($query) {
                                    $query->select('client_id')
                                          ->from('employee_client_assignments')
                                          ->where('employee_id', Auth::id())
                                          ->where('is_active', true);
                                })->sum('balance');
                            @endphp
                            <h4 class="text-info">€{{ number_format($totalBalance, 2, ',', '.') }}</h4>
                            <p class="mb-0">Saldo Totale Gestito</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ date('d/m/Y') }}</h4>
                            <p class="mb-0">Oggi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection