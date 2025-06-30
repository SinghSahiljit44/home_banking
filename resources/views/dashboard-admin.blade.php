@extends('layouts.bootstrap')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-shield-alt me-2"></i>Dashboard Amministratore</h2>
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

    <!-- Statistiche Generali -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ App\Models\User::count() }}</h4>
                    <p class="mb-0">Utenti Totali</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-university fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ App\Models\Account::count() }}</h4>
                    <p class="mb-0">Conti Attivi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-exchange-alt fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ App\Models\Transaction::count() }}</h4>
                    <p class="mb-0">Transazioni</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-euro-sign fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">€{{ number_format(App\Models\Account::sum('balance'), 2, ',', '.') }}</h4>
                    <p class="mb-0">Saldo Totale</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Principale -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-transparent border-primary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>Gestione Utenti</h5>
                    <p class="small text-muted">Amministra utenti e permessi</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-transparent border-success h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-3x text-success mb-3"></i>
                    <h5>Transazioni</h5>
                    <p class="small text-muted">Monitora e gestisci transazioni</p>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-success">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-transparent border-info h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie fa-3x text-info mb-3"></i>
                    <h5>Assegnazioni</h5>
                    <p class="small text-muted">Gestisci Employee-Client</p>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-info">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-transparent border-warning h-100">
                <div class="card-body text-center">
                    <i class="fas fa-key fa-3x text-warning mb-3"></i>
                    <h5>Recupero Credenziali</h5>
                    <p class="small text-muted">Reset password e username</p>
                    <a href="{{ route('admin.password-recovery.index') }}" class="btn btn-warning">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-transparent border-danger h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-danger mb-3"></i>
                    <h5>Report</h5>
                    <p class="small text-muted">Statistiche e report avanzati</p>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-danger">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-transparent border-secondary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-university fa-3x text-secondary mb-3"></i>
                    <h5>Conti</h5>
                    <p class="small text-muted">Gestione conti correnti</p>
                    <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">
                        Accedi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection