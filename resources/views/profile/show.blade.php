@extends('layouts.bootstrap')

@section('title', 'Il Mio Profilo')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-user me-2"></i>Il Mio Profilo</h4>
                        <div>
                            <a href="{{ route('client.profile.edit') }}" class="btn btn-warning me-2">
                                <i class="fas fa-edit me-1"></i>Modifica
                            </a>
                            @if(Auth::user()->isClient())
                                <a href="{{ route('dashboard.cliente') }}" class="btn btn-outline-light">
                                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                                </a>
                            @elseif(Auth::user()->isEmployee())
                                <a href="{{ route('dashboard.employee') }}" class="btn btn-outline-light">
                                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                                </a>
                            @else
                                <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-light">
                                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Informazioni Base -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-id-card me-2"></i>Informazioni Personali</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nome Completo:</strong> {{ $user->full_name }}</p>
                                    <p><strong>Username:</strong> {{ $user->username }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Telefono:</strong> {{ $user->phone ?: 'Non specificato' }}</p>
                                    <p><strong>Ruolo:</strong> 
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'employee' ? 'warning' : 'success') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </p>
                                    <p><strong>Membro dal:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            @if($user->address)
                                <div class="row">
                                    <div class="col-12">
                                        <p><strong>Indirizzo:</strong> {{ $user->address }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informazioni Conto (solo per clienti) -->
                    @if($user->isClient() && $user->account)
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-university me-2"></i>Il Mio Conto</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Numero Conto:</strong> {{ $user->account->account_number }}</p>
                                        <p><strong>IBAN:</strong> <span class="font-monospace">{{ $user->account->iban }}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Saldo:</strong> 
                                            <span class="text-success h5">€{{ number_format($user->account->balance, 2, ',', '.') }}</span>
                                        </p>
                                        <p><strong>Stato:</strong> 
                                            <span class="badge {{ $user->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $user->account->is_active ? 'Attivo' : 'Sospeso' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Sicurezza -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-shield-alt me-2"></i>Sicurezza</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Password:</strong> ••••••••</p>
                                    <a href="{{ route('client.profile.change-password') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-key me-1"></i>Cambia Password
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    @if($user->isClient())
                                        <p><strong>Domanda di Sicurezza:</strong> 
                                            @if($user->securityQuestion)
                                                <span class="text-success">Configurata</span>
                                            @else
                                                <span class="text-warning">Non configurata</span>
                                            @endif
                                        </p>
                                        <a href="{{ route('client.security.questions') }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-question-circle me-1"></i>Gestisci Sicurezza
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiche per Employee -->
                    @if($user->isEmployee())
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-chart-bar me-2"></i>Le Tue Statistiche</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-success">
                                            {{ $user->assignedClients()->whereHas('account', function($q) { 
                                                $q->where('is_active', true); 
                                            })->count() }}
                                        </h4>
                                        <p class="mb-0">Conti Attivi</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-info">
                                            €{{ number_format($user->assignedClients()->whereHas('account')->get()->sum(function($client) { 
                                                return $client->account->balance ?? 0; 
                                            }), 2, ',', '.') }}
                                        </h4>
                                        <p class="mb-0">Saldo Gestito</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Azioni -->
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('client.profile.edit') }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifica Profilo
                        </a>
                        <a href="{{ route('client.profile.change-password') }}" class="btn btn-outline-warning">
                            <i class="fas fa-key me-2"></i>Cambia Password
                        </a>
                        @if($user->isClient())
                            <a href="{{ route('client.security.questions') }}" class="btn btn-outline-info">
                                <i class="fas fa-shield-alt me-2"></i>Sicurezza
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection