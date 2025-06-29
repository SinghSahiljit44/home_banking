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
                        <li><a class="dropdown-item" href="{{ route('client.profile.show') }}">
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
                    <h5>Depositi</h5>
                    <p class="small text-muted">Depositi per tutti i clienti</p>
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
                            <h4 class="text-primary">{{ Auth::user()->assignedClients()->count() }}</h4>
                            <p class="mb-0">Clienti Assegnati</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">
                                {{ Auth::user()->assignedClients()->whereHas('account', function($q) { $q->where('is_active', true); })->count() }}
                            </h4>
                            <p class="mb-0">Conti Attivi</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">
                                €{{ number_format(Auth::user()->assignedClients()->whereHas('account')->get()->sum(function($client) { return $client->account->balance ?? 0; }), 2, ',', '.') }}
                            </h4>
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

    <!-- Azioni Rapide -->
    <div class="row">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-plus-circle me-2"></i>Azioni Rapide</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('employee.clients.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Registra Nuovo Cliente
                        </a>
                        <a href="{{ route('employee.universal.clients') }}" class="btn btn-success">
                            <i class="fas fa-coins me-2"></i>Effettua Deposito
                        </a>
                        <a href="{{ route('employee.password-recovery.index') }}" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Reset Password Cliente
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Attività Recenti</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-transparent border-secondary">
                            <small class="text-muted">{{ now()->format('H:i') }}</small>
                            <br>Accesso effettuato alla dashboard
                        </div>
                        @if(Auth::user()->assignedClients()->whereHas('account.allTransactions', function($q) { 
                            $q->whereDate('created_at', today()); 
                        })->exists())
                            <div class="list-group-item bg-transparent border-secondary">
                                <small class="text-muted">Oggi</small>
                                <br>Nuove transazioni dai tuoi clienti
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection